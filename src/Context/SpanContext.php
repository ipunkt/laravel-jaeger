<?php namespace Ipunkt\LaravelJaeger\Context;

use Illuminate\Support\Collection;
use Ipunkt\LaravelJaeger\Context\ContextArrayConverter\ContextArrayConverter;
use Ipunkt\LaravelJaeger\Context\Exceptions\NoSpanException;
use Ipunkt\LaravelJaeger\Context\Exceptions\NoTracerException;
use Ipunkt\LaravelJaeger\Context\Exceptions\NoUberTraceIdException;
use Ipunkt\LaravelJaeger\Context\Exceptions\NoXTraceException;
use Ipunkt\LaravelJaeger\LogCleaner\LogCleaner;
use Jaeger\Codec\CodecInterface;
use Jaeger\Log\UserLog;
use Jaeger\Span\Span;
use Jaeger\Span\SpanInterface;
use Jaeger\Tag\StringTag;
use Jaeger\Tracer\Tracer;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Class MessageContext
 */
class SpanContext implements Context
{

    /**
     * @var Tracer
     */
    protected $tracer;

    /**
     * @var Span
     */
    protected $messageSpan;

    /**
     * @var UuidInterface
     */
    protected $uuid;

	/**
	 * @var LogCleaner
	 */
	protected $logCleaner;

	/**
	 * @var CodecInterface
	 */
	private $codec;
	/**
	 * @var ContextArrayConverter
	 */
	private $contextArrayConverter;

	/**
	 * MessageContext constructor.
	 * @param CodecInterface $codec
	 * @param ContextArrayConverter $contextArrayConverter
	 * @param LogCleaner $logCleaner
	 */
    public function __construct(
								CodecInterface $codec,
								ContextArrayConverter $contextArrayConverter,
								LogCleaner $logCleaner)
    {
	    $this->logCleaner = $logCleaner;
	    $this->codec = $codec;
	    $this->contextArrayConverter = $contextArrayConverter;
    }

    public function start()
    {
    }

    public function finish()
    {
        $this->tracer->finish($this->messageSpan);
    }

	public function fromUberId( string $name, string $uberId ) {
		$this->assertHasTracer();

		$filledUberId = $this->fillUberIdIfNecessary($uberId);

		$context = $this->codec->decode($filledUberId);
		$this->messageSpan = $this->tracer->start($name, [], $context);
		return $this;
    }

	private function fillUberIdIfNecessary( string $uberId ) {
		$parts = collect(explode(':', $uberId));

		while($parts->count() < 4)
			$parts->push('0');

		return $parts->implode(':');
	}

    public function parse(string $name, array $data)
    {
    	$this->assertHasTracer();

	    $context = $this->parseContext( collect($data) );
	    $this->messageSpan= $this->tracer->start($name, [], $context);

        // Set the uuid as a tag for this trace
        $this->uuid = Uuid::uuid1();
        $this->setPrivateTags([
	        'uuid' => (string)$this->uuid,
	        'environment' => app()->environment(),
        ]);
    }

	/**
	 * @param array $data
	 * @return \Jaeger\Span\Context\SpanContext|null
	 */
	protected function parseContext( Collection $data ) {
		try {
			return $this->parseUberTraceId($data);
		} catch(NoUberTraceIdException $exception) {
			// continue
		}

		try {
			return $this->parseXTraceId($data);
		} catch(NoXTraceException $e) {
			//continue
		}

		return null;
	}

	protected function parseUberTraceId(Collection $data) {
		$uberTraceId = $data->first(function($value, $key) {
			if(strtolower($key) === 'uber-trace-id') {
				return true;
			}
			return false;
		});

		if( empty($uberTraceId) )
			throw new NoUberTraceIdException();

		$context = $this->codec->decode( $uberTraceId );
		return $context;
	}

	protected function parseXTraceId(Collection $data) {
		$xTraceId = $data->first(function($value, $key) {
			if(strtolower($key) === 'x-trace') {
				return true;
			}
			return false;
		});

		if( empty($xTraceId) )
			throw new NoXTraceException();

		$data = json_decode( $xTraceId, true );

		$context = $this->contextArrayConverter
			->extract($data)
			->getContext();

		return $context;
	}

    public function setPrivateTags(array $tags)
    {
        foreach($tags as $name => $value)
            $this->messageSpan->addTag( new StringTag($name, $value) );
    }

	/**
	 * @param array $tags
	 * @deprecated
	 */
    public function setPropagatedTags(array $tags)
    {
        $this->setPrivateTags($tags);
    }

    /**
     * @param array $messageData
     */
    public function inject(array &$messageData)
    {
    	$this->assertHasTracer();
    	$this->assertHasSpan();

        $context = $this->messageSpan->getContext();

        $xtraceData = [];
        $this->contextArrayConverter->setContext($this->messageSpan->getContext())->inject($xtraceData);
        $messageData['x-trace'] = json_encode($xtraceData);
        $messageData['uber-trace-id'] = $this->codec->encode($context);
    }

	public function log( array $fields ) {
    	$this->logCleaner->setLogs($fields)->clean();
    	foreach($this->logCleaner->getLogs() as $logKey => $logValue) {
    	    if(!is_string($logValue))
    	        $logValue = json_encode($logValue);

            $this->messageSpan->addLog(new UserLog($logKey, 'info', $logValue));
        }
	}

	private function assertHasTracer() {
    	if($this->tracer instanceof Tracer)
    		return;

    	throw new NoTracerException();
	}

	private function assertHasSpan() {
    	if($this->messageSpan instanceof SpanInterface)
    		return;

    	throw new NoSpanException();
	}

    public function child($name): Context
    {
        $context = app(SpanContext::class);
        $context->tracer = $this->tracer;
        $context->messageSpan = $this->tracer->start($name, [], $this->messageSpan->getContext());
        app()->instance('current-context', $context);
        return new WrapperContext($context, $this);
	}
}
