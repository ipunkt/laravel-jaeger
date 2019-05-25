<?php namespace Ipunkt\LaravelJaegerTests\SpanContext;

use Ipunkt\LaravelJaeger\Context\SpanContext;
use Ipunkt\LaravelJaeger\Context\TracerBuilder\TracerBuilder;
use Ipunkt\LaravelJaeger\SpanExtractor\SpanExtractor;
use Ipunkt\LaravelJaeger\TagPropagator\TagPropagator;
use Ipunkt\LaravelJaegerTests\TestCase;
use Jaeger\Jaeger;
use Jaeger\Span;
use Mockery;
use Mockery\MockInterface;

/**
 * Class SpanContextTest
 * @package Ipunkt\LaravelJaegerTests\SpanContext
 */
class SpanContextTest extends TestCase {

	/**
	 * @var SpanContext
	 */
	protected $context;

	/**
	 * @var TracerBuilder|MockInterface
	 */
	protected $tracerBuilder;

	/**
	 * @var TagPropagator|MockInterface
	 */
	protected $tagPropagator;

	/**
	 * @var SpanExtractor|MockInterface
	 */
	protected $spanExtractor;

	/**
	 * @var \OpenTracing\SpanContext|Mockery
	 */
	protected $spanContext;

	/**
	 * @var Jaeger|MockInterface
	 */
	protected $tracer;

	/**
	 * @var \OpenTracing\Span|MockInterface
	 */
	protected $span;

	public function setUp(): void {
		parent::setUp();

		$this->buildMocks();

		$this->context = new SpanContext($this->tagPropagator, $this->spanExtractor, $this->tracerBuilder);
	}


	/**
	 * @test
	 */
	public function startBuildsTracer() {
		$this->assertTracerIsBuilt();
		$this->context->start();
	}

	/**
	 * @test
	 */
	public function finish() {

		$this->assertSpanIsFinished();
		$this->assertDataIsFlushedToTracer();

		$this->setUpContext();
		$this->context->finish();
	}

	/**
	 * @test
	 */
	public function injectAddsPropagatedTags(  ) {
		$this->setUpContext();
		$this->context->setPropagatedTags([
			'tag1' => 'value1',
			'tag2' => 'value2',
		]);

		$data = [];
		$this->context->inject($data);
		$this->arrayHasKey('trace', $data);
	}

	private function buildMocks() {
		$this->tagPropagator = Mockery::mock(TagPropagator::class);
		$this->tagPropagator->shouldIgnoreMissing($this->tagPropagator);
		$this->spanExtractor = Mockery::mock(SpanExtractor::class);
		$this->spanExtractor->shouldIgnoreMissing($this->spanExtractor);
		$this->tracerBuilder = Mockery::mock(TracerBuilder::class);
		$this->tracer = Mockery::mock(Jaeger::class);
		$this->tracer->shouldIgnoreMissing($this->tracer);
		$this->span = Mockery::mock(Span::class);
		$this->span->shouldIgnoreMissing($this->span);
		$this->spanContext = Mockery::mock(\OpenTracing\SpanContext::class);
		$this->span->shouldReceive('getContext')->andReturn($this->spanContext);
		$this->spanExtractor->shouldReceive('getBuiltSpan')->andReturn($this->span);
	}

	private function assertTracerIsBuilt() {
		$this->tracerBuilder->shouldReceive('build')->once();
	}

	private function assertSpanIsFinished() {
		$this->span->shouldReceive('finish')->once();
	}

	private function assertDataIsFlushedToTracer() {
		$this->tracer->shouldReceive('flush')->once();
	}

	private function setUpContext() {
		$this->useGenericTracer();

		$this->context->start();
		$this->context->parse('', []);
	}

	private function useGenericTracer() {
		$this->tracerBuilder->shouldReceive('build')->andReturn($this->tracer);
	}


}