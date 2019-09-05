<?php namespace Ipunkt\LaravelJaeger\Context\TracerBuilder;

use Jaeger\Tracer\Tracer;

/**
 * Class TracerBuilder
 * @package Ipunkt\LaravelJaeger\Context\TracerBuilder
 */
class TracerBuilder {

	public function build() {
	    return app(Tracer::class);
	}

}