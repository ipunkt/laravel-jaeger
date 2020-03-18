<?php namespace Ipunkt\LaravelJaegerTests\UnitTests\TagPropagator;

use Ipunkt\LaravelJaeger\TagPropagator\TagPropagator;
use Ipunkt\LaravelJaegerTests\TestCase;

/**
 * Class TagPropagatorTest
 * @package Ipunkt\LaravelJaegerTests\TagPropagator
 */
class TagPropagatorTest extends TestCase {

	/**
	 * @var TagPropagator
	 */
	protected $tagPropagator;

	public function setUp(): void {
		parent::setUp();
		$this->tagPropagator = new TagPropagator();
	}

	/**
	 * @test
	 */
	public function injectsTags() {
		$this->tagPropagator->addTags([
			'tag1' => 'value1'
		]);

		$data = [];
		$this->tagPropagator->inject($data);

		$this->assertArrayHasKey('propagated-tags', $data);
		$this->assertArrayHasKey('tag1', $data['propagated-tags']);
		$this->assertEquals('value1', $data['propagated-tags']['tag1']);
	}

	/**
	 * @test
	 */
	public function extractsTags() {

		$data = [
			'propagated-tags' => [
				'tag1' => 'value1'
			]
		];

		$this->tagPropagator->extract($data);

		$resultData = [];
		$this->tagPropagator->inject($resultData);
		$this->assertArrayHasKey('propagated-tags', $resultData);
		$this->assertArrayHasKey('tag1', $resultData['propagated-tags']);
		$this->assertEquals('value1', $resultData['propagated-tags']['tag1']);
	}
}