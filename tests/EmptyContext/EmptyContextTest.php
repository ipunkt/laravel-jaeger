<?php namespace Ipunkt\LaravelJaegerTests\EmptyContext;

use Ipunkt\LaravelJaeger\Context\EmptyContext;
use Ipunkt\LaravelJaegerTests\TestCase;

/**
 * Class EmptyContextTest
 * @package Ipunkt\LaravelJaegerTests\EmptyContext
 */
class EmptyContextTest extends TestCase {

	/**
	 * @var EmptyContext
	 */
	protected $emptyContext;

	/**
	 *
	 */
	public function setUp(): void {
		parent::setUp();
		$this->emptyContext = new EmptyContext();
	}

	/**
	 * @test
	 */
	public function finishCallableWithoutPreparations() {

		$this->emptyContext->finish();
		$this->addToAssertionCount(1);

	}

	/**
	 * @test
	 */
	public function setPrivateTagsCallableWithoutPreparations() {

		$this->emptyContext->setPrivateTags([
			'a' => 'b'
		]);
		$this->addToAssertionCount(1);

	}

	/**
	 * @test
	 */
	public function setPropagatedTagsCallableWithoutPreparations() {

		$this->emptyContext->setPropagatedTags([
			'a' => 'b'
		]);
		$this->addToAssertionCount(1);

	}

	/**
	 * @test
	 */
	public function logCallableWithoutPreparations() {

		$this->emptyContext->log([
			'a' => 'b'
		]);
		$this->addToAssertionCount(1);

	}

	/**
	 * @test
	 */
	public function injectCallableWithoutPreparations() {

		$data = [];
		$this->emptyContext->inject($data);
		$this->addToAssertionCount(1);

	}

	/**
	 * @test
	 */
	public function parseCallableWithoutPreparations() {

		$this->emptyContext->parse('', []);
		$this->addToAssertionCount(1);

	}

}