<?php namespace Ipunkt\LaravelJaeger\Context;

use Illuminate\Support\Collection;
use Ipunkt\LaravelJaeger\Context\ContextArrayConverter\ContextArrayConverter;
use Ipunkt\LaravelJaeger\Context\Exceptions\NoSpanException;
use Ipunkt\LaravelJaeger\Context\Exceptions\NoTracerException;
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
	 * @var Collection|CodecInterface[]
	 */
	protected $injectCodecs;

	/**
	 * @var Collection|CodecInterface[]
	 */
	protected $extractCodecs;

	/**
	 * @var ContextArrayConverter
	 */
	private $contextArrayConverter;

	/**
	 * MessageContext constructor.
	 * @param ContextArrayConverter $contextArrayConverter
	 * @param LogCleaner $logCleaner
	 */
    public function __construct(ContextArrayConverter $contextArrayConverter,
								LogCleaner $logCleaner)
    {
	    $this->logCleaner = $logCleaner;
	    $this->contextArrayConverter = $contextArrayConverter;
	    $this->extractCodecs = collect();
	    $this->injectCodecs = collect();
    }

    public function start()
    {
    }

    public function finish()
    {
        $this->tracer->finish($this->messageSpan);
    }

    public function parse(string $name, array $data)
    {
    	$this->assertHasTracer();

	    $context = $this->parseContext( collect($data) );
	    $this->messageSpan = $this->tracer->start($name, [], $context);

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
		$context = null;
		$this->extractCodecs->each(function(CodecInterface $codec, $headerName) use ($data, &$context) {
			$value = $this->getHeader($data, $headerName);

			if( empty($value) )
				return null;

			$context = $codec->decode($value);
			return false;
		});

		if($context === null)
			return null;

		return $context;
	}

	protected function getHeader( Collection $data, $headerName ) {
		$headerValue = $data->first(function($value, $key) use ($headerName) {
			if(strtolower($key) === strtolower($headerName)) {
				return true;
			}
			return false;
		});

		return $headerValue;
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

        $this->injectCodecs->each(function(CodecInterface $codec, $headerName) use (&$messageData) {
	        $context = $this->messageSpan->getContext();
        	$messageData[$headerName] = $codec->encode($context);
        });
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

	public function registerInjectCodec($headerName, CodecInterface $codec) {
    	$this->injectCodecs->put($headerName, $codec);
	}

	public function registerExtractCodec($headerName, CodecInterface $codec) {
    	$this->extractCodecs->put($headerName, $codec);
	}
}
