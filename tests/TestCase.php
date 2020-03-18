<?php namespace Ipunkt\LaravelJaegerTests;

use Ipunkt\LaravelJaeger\Provider;

/**
 * Class TestCase
 * @package Ipunkt\LaravelJaegerTests
 */
class TestCase extends \Orchestra\Testbench\TestCase {

	protected function getPackageProviders($app) {
		return [
			Provider::class,
		];
	}

}