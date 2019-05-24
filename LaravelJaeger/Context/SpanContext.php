<?php namespace Ipunkt\LaravelJaeger\Context;

use Ipunkt\LaravelJaeger\SpanExtractor\SpanExtractor;
use Ipunkt\LaravelJaeger\TagPropagator\TagPropagator;
use Jaeger\Config;
use Jaeger\Jaeger;
use OpenTracing\Span;
use const OpenTracing\Formats\TEXT_MAP;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Class MessageContext
 */
class SpanContext implements Context
{

    /**
     * @var Jaeger
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
	 * MessageContext constructor.
	 * @param TagPropagator $tagPropagator
	 * @param SpanExtractor $spanExtractor
	 */
    public function __construct(TagPropagator $tagPropagator, SpanExtractor $spanExtractor) {
        $this->tagPropagator = $tagPropagator;
	    $this->spanExtractor = $spanExtractor;
    }

    public function start()
    {
        $this->buildTracer();
    }

    public function finish()
    {
        $this->messageSpan->finish();
        $this->tracer->flush();
    }

    protected function buildTracer(): void
    {
        $config = Config::getInstance();

        $config->gen128bit();

        // Start the tracer with a service name and the jaeger address
        $this->tracer = $config->initTrace(config('app.name'), config('jaeger.host'));
    }

    public function parse($name, $data)
    {
    	$this->messageSpan = $this->spanExtractor
		    ->setName($name)
		    ->setData($data)
		    ->setTracer($this->tracer)
		    ->setTagPropagator($this->tagPropagator)
		    ->extract();

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
        $this->messageSpan->setTags($tags);
    }

    public function setPropagatedTags(array $tags)
    {
        $this->tagPropagator->addTags($tags);

        $this->messageSpan->setTags($tags);
    }

    /**
     * @param array $messageData
     */
    public function inject(array &$messageData)
    {
        $context = $this->messageSpan->getContext();

        app('context.tracer')->inject($context, TEXT_MAP, $messageData);

        $this->tagPropagator->inject($messageData);
    }

	public function log( array $fields ) {
    	$this->messageSpan->log($fields);
	}
}