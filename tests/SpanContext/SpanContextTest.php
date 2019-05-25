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
	protected $spanContext;

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

		$this->spanContext = new SpanContext($this->tagPropagator, $this->spanExtractor, $this->tracerBuilder);
	}


	/**
	 * @test
	 */
	public function startBuildsTracer() {
		$this->assertTracerIsBuilt();
		$this->spanContext->start();
	}

	/**
	 * @test
	 */
	public function finish() {
		$this->useGenericTracer();

		$this->assertSpanIsFinished();
		$this->assertDataIsFlushedToTracer();

		$this->spanContext->start();
		$this->spanContext->parse('', []);
		$this->spanContext->finish();
	}

	private function buildMocks() {
		$this->tagPropagator = Mockery::mock(TagPropagator::class);
		$this->tagPropagator->shouldIgnoreMissing($this->tagPropagator);
		$this->spanExtractor = Mockery::mock(SpanExtractor::class);
		$this->spanExtractor->shouldIgnoreMissing($this->spanExtractor);
		$this->tracerBuilder = Mockery::mock(TracerBuilder::class);
		$this->tracer = Mockery::mock(Jaeger::class);
		$this->span = Mockery::mock(Span::class);
		$this->span->shouldIgnoreMissing($this->span);
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

	private function useGenericTracer() {
		$this->tracerBuilder->shouldReceive('build')->andReturn($this->tracer);
	}


}