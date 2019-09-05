<?php namespace Ipunkt\LaravelJaeger\Context;

use Ipunkt\LaravelJaeger\Context\Exceptions\NoSpanException;
use Ipunkt\LaravelJaeger\Context\Exceptions\NoTracerException;
use Ipunkt\LaravelJaeger\Context\TracerBuilder\TracerBuilder;
use Ipunkt\LaravelJaeger\LogCleaner\LogCleaner;
use Ipunkt\LaravelJaeger\SpanExtractor\SpanExtractor;
use Ipunkt\LaravelJaeger\TagPropagator\TagPropagator;
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
    private $tagPropagator;
	/**
	 * @var SpanExtractor
	 */
	private $spanExtractor;
	/**
	 * @var TracerBuilder
	 */
	private $tracerBuilder;
	/**
	 * @var LogCleaner
	 */
	private $logCleaner;

	/**
	 * MessageContext constructor.
	 * @param TagPropagator $tagPropagator
	 * @param SpanExtractor $spanExtractor
	 * @param TracerBuilder $tracerBuilder
	 * @param LogCleaner $logCleaner
	 */
    public function __construct(TagPropagator $tagPropagator,
                                SpanExtractor $spanExtractor,
                                TracerBuilder $tracerBuilder,
								LogCleaner $logCleaner) {
        $this->tagPropagator = $tagPropagator;
	    $this->spanExtractor = $spanExtractor;
	    $this->tracerBuilder = $tracerBuilder;
	    $this->logCleaner = $logCleaner;
    }

    public function start()
    {
        $this->buildTracer();
    }

    public function finish()
    {
        $this->tracer->finish($this->messageSpan);
        $this->tracer->flush();
    }

    protected function buildTracer(): void
    {
    	$this->tracer = $this->tracerBuilder->build();
    }

    public function parse(string $name, array $data)
    {
    	$this->assertHasTracer();

    	$this->messageSpan = $this->spanExtractor
		    ->setName($name)
		    ->setData($data)
		    ->setTracer($this->tracer)
		    ->setTagPropagator($this->tagPropagator)
		    ->extract()
	        ->getBuiltSpan();

        // Set the uuid as a tag for this trace
        $this->uuid = Uuid::uuid1();
        $this->setPrivateTags([
	        'uuid' => (string)$this->uuid,
	        'environment' => config('app.env')
        ]);
        $this->tagPropagator->apply($this->messageSpan);
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

        Arr::set($messageData);

        $this->tagPropagator->inject($messageData);
    }

	public function log( array $fields ) {
    	$this->logCleaner->setLogs($fields)->clean();
    	foreach($this->logCleaner->getLogs() as $logKey => $logValue)
            $this->messageSpan->addLog(new UserLog($logKey, 'info', $logValue));
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
}