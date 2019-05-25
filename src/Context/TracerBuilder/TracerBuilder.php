<?php namespace Ipunkt\LaravelJaeger\Context\TracerBuilder;

use Jaeger\Config;

/**
 * Class TracerBuilder
 * @package Ipunkt\LaravelJaeger\Context\TracerBuilder
 */
class TracerBuilder {
	/**
	 * @var Config
	 */
	protected $config;

	/**
	 * @var string
	 */
	protected $name = '';

	/**
	 * @var string
	 */
	protected $jaegerHost = '';

	/**
	 * TracerBuilder constructor.
	 * @param Config $config
	 */
	public function __construct( Config $config) {
		$this->config = $config;
	}

	public function build() {
		// Start the tracer with a service name and the jaeger address
		return $this->config->initTrace($this->name, $this->jaegerHost);
	}

	/**
	 * @param string $name
	 * @return TracerBuilder
	 */
	public function setName( string $name ): TracerBuilder {
		$this->name = $name;
		return $this;
	}

	/**
	 * @param string $jaegerHost
	 * @return TracerBuilder
	 */
	public function setJaegerHost( string $jaegerHost ): TracerBuilder {
		$this->jaegerHost = $jaegerHost;
		return $this;
	}

}