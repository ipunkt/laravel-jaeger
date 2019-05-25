<?php namespace Ipunkt\LaravelJaegerTests\SpanContext;

use Ipunkt\LaravelJaeger\Context\SpanContext;
use Ipunkt\LaravelJaeger\Context\TracerBuilder\TracerBuilder;
use Ipunkt\LaravelJaeger\SpanExtractor\SpanExtractor;
use Ipunkt\LaravelJaeger\TagPropagator\TagPropagator;
use Ipunkt\LaravelJaegerTests\TestCase;
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
	 * @var
	 */
	protected $tracer;

	public function setUp(): void {
		parent::setUp();

		$this->tagPropagator = Mockery::mock(TagPropagator::class);
		$this->tagPropagator->shouldIgnoreMissing($this->tagPropagator);
		$this->spanExtractor = Mockery::mock(SpanExtractor::class);
		$this->spanExtractor->shouldIgnoreMissing($this->spanExtractor);
		$this->tracerBuilder = Mockery::mock(TracerBuilder::class);

		$this->spanContext = new SpanContext($this->tagPropagator, $this->spanExtractor, $this->tracerBuilder);
	}


	/**
	 * @test
	 */
	public function startBuildsTracer() {
		$this->tracerBuilder->shouldReceive('build')->once();
		$this->spanContext->start();
	}

}