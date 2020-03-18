<?php namespace Ipunkt\LaravelJaeger\Context;

use Illuminate\Support\Arr;
use Ipunkt\LaravelJaeger\Context\Exceptions\NoSpanException;
use Ipunkt\LaravelJaeger\Context\Exceptions\NoTracerException;
use Ipunkt\LaravelJaeger\LogCleaner\LogCleaner;
use Ipunkt\LaravelJaeger\TagPropagator\TagPropagator;
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
     * @var TagPropagator
     */
    protected $tagPropagator;
	/**
	 * @var LogCleaner
	 */
	protected $logCleaner;

	/**
	 * @var CodecInterface
	 */
	private $codec;

	/**
	 * MessageContext constructor.
	 * @param TagPropagator $tagPropagator
	 * @param CodecInterface $codec
	 * @param LogCleaner $logCleaner
	 */
    public function __construct(TagPropagator $tagPropagator,
								CodecInterface $codec,
								LogCleaner $logCleaner)
    {
        $this->tagPropagator = $tagPropagator;
	    $this->logCleaner = $logCleaner;
	    $this->codec = $codec;
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

    	$context = $this->codec->decode( Arr::get($data, 'uber-trace-id') );
    	$this->tagPropagator->extract( $data );
    	$span = $this->tracer->start($name, [], $context);

        // Set the uuid as a tag for this trace
        $this->uuid = Uuid::uuid1();
        $this->setPrivateTags([
	        'uuid' => (string)$this->uuid,
	        'environment' => config('app.env')
        ]);
        $this->tagPropagator->apply($span);

	    $this->messageSpan = $span;
    }

    public function setPrivateTags(array $tags)
    {
        foreach($tags as $name => $value)
            $this->messageSpan->addTag( new StringTag($name, $value) );
    }

    public function setPropagatedTags(array $tags)
    {
        $this->tagPropagator->addTags($tags);

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

        $messageData['uber-trace-id'] = $this->codec->encode($context);

        $this->tagPropagator->inject($messageData);
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
        // TODO: return wrapper
        return new WrapperContext($context, $this);
	}
}