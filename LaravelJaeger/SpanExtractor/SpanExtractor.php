<?php namespace Ipunkt\LaravelJaeger\SpanExtractor;

use Ipunkt\LaravelJaeger\TagPropagator\TagPropagator;
use Jaeger\Jaeger;
use OpenTracing\Reference;
use OpenTracing\SpanContext;

/**
 * Class SpanExtractor
 * @package Ipunkt\LaravelJaeger\SpanExtractor
 */
class SpanExtractor {

	protected $name = '';

	protected $data = [];

	/**
	 * @var TagPropagator
	 */
	protected $tagPropagator;

	/**
	 * @var array
	 */
	protected $traceContent;

	/**
	 * @var \OpenTracing\SpanContext
	 */
	protected $spanContext;

	/**
	 * @var Jaeger
	 */
	protected $tracer;

	public function extract() {
		$this->parseContext();

		$this->buildSpanOptions();

		// Start the global span, it'll wrap the request/console lifecycle
		return $this->tracer->startSpan($this->name, $this->spanOptions);
	}

	private function parseContext() {
		$this->resetContext();

		$this->extractContextFromData();
	}

	private function resetContext()
	{
		$this->spanContext = null;
		$this->tagPropagator->reset();
	}

	private function extractContextFromData()
	{
		if( !array_key_exists('trace', $this->data) )
			return;

		$this->traceContent = $this->data['trace'];

		$this->extractSpanContext();

		$this->extractPropagatedTags();
	}

	private function extractSpanContext()
	{
		$this->spanContext = $this->tracer->extract(TEXT_MAP, $this->traceContent);
	}

	private function extractPropagatedTags()
	{
		$this->tagPropagator->extract($this->traceContent);
	}

	/**
	 * @var array
	 */
	private $spanOptions = [];

	private function buildSpanOptions()
	{
		$this->spanOptions = [];

		$this->addChildOfSpanOption();
	}

	private function addChildOfSpanOption()
	{
		$spanContextSet = ($this->spanContext instanceof SpanContext);
		if( !$spanContextSet )
			return;

		$this->spanOptions[Reference::CHILD_OF] = $this->spanContext;
	}

	/**
	 * @param string $name
	 * @return SpanExtractor
	 */
	public function setName( string $name ): SpanExtractor {
		$this->name = $name;
		return $this;
	}

	/**
	 * @param array $data
	 * @return SpanExtractor
	 */
	public function setData( array $data ): SpanExtractor {
		$this->data = $data;
		return $this;
	}

	/**
	 * @param TagPropagator $tagPropagator
	 * @return SpanExtractor
	 */
	public function setTagPropagator( TagPropagator $tagPropagator ): SpanExtractor {
		$this->tagPropagator = $tagPropagator;
		return $this;
	}

	public function setTracer( \Jaeger\Jaeger $tracer ) {
		$this->tracer =$tracer;
		return $this;
	}

}