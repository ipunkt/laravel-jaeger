<?php namespace Ipunkt\LaravelJaegerTests\TracerBuilder;

use Ipunkt\LaravelJaeger\Context\TracerBuilder\TracerBuilder;
use Ipunkt\LaravelJaegerTests\TestCase;
use Jaeger\Config;
use Mockery\MockInterface;

/**
 * Class TracerBuilderTest
 * @package Ipunkt\LaravelJaegerTests\TracerBuilder
 */
class TracerBuilderTest extends TestCase {

	/**
	 * @var Config|MockInterface
	 */
	protected $config;

	/**
	 * @var TracerBuilder
	 */
	protected $tracerBuilder;

	public function setUp(): void {
		parent::setUp();

		$this->config = \Mockery::mock( Config::class );

		$this->tracerBuilder = new TracerBuilder($this->config);
	}

	/**
	 * @test
	 */
	public function initializesTracer() {

		$testTracer = 5;

		$this->config->shouldReceive('initTrace')
			->once()
			->with('name', 'host')
			->andReturn($testTracer);

		$tracer = $this->tracerBuilder
			->setName('name')
			->setJaegerHost('host')
			->build();

		$this->assertEquals($testTracer, $tracer, 'Tracer was not returned');
	}

}