<?php namespace Ipunkt\LaravelJaegerTests\LogCleaner;

use Ipunkt\LaravelJaeger\LogCleaner\LogCleaner;
use Ipunkt\LaravelJaegerTests\TestCase;

/**
 * Class LogCleanerTest
 * @package Ipunkt\LaravelJaegerTests\LogCleaner
 */
class LogCleanerTest extends TestCase {

	/**
	 * @var LogCleaner
	 */
	protected $logCleaner;

	public function setUp():void {
		$this->logCleaner = new LogCleaner();
	}

	/**
	 * @test
	 */
	public function reducesStringSize() {
		$this->logCleaner
			->setMaxLength(10)
			->setCutoffIndicator('...')
			->setLogs([
			'message' => '1234567890acbdefgh'
		])->clean();

		$logs = $this->logCleaner->getLogs();
		$this->assertEquals('1234567890...', $logs['message']);
	}
	/**
	 * @test
	 */
	public function reducesStringSizeInSubarrays() {
		$this->logCleaner
			->setMaxLength(10)
			->setCutoffIndicator('...')
			->setLogs([
				'subarray' => [
					'toolong' => '1234567890abcdefg'
				]
			])->clean();

		$logs = $this->logCleaner->getLogs();
		$this->assertEquals('1234567890...', $logs['subarray']['toolong']);
	}

	/**
	 * @test
	 */
	public function honorsCutoffIndicator() {
		$this->logCleaner
			->setMaxLength(1)
			->setCutoffIndicator('+++')
			->setLogs([
				'message' => '123'
			])->clean();

		$logs = $this->logCleaner->getLogs();
		$this->assertEquals('1+++', $logs['message']);
	}


	/**
	 * @test
	 */
	public function doesNotErrorOnObjectEntries() {
		$this->logCleaner
			->setMaxLength(1)
			->setCutoffIndicator('+++')
			->setLogs([
				'object' => new \stdClass(),
				'subarray' => [
					'anotherobject' => new \stdClass()
				],
				'message' => '123'
			])->clean();

		$logs = $this->logCleaner->getLogs();
		$this->assertEquals('1+++', $logs['message']);
	}
}