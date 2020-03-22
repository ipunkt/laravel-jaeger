<?php namespace Ipunkt\LaravelJaeger\Codec;

use Ipunkt\LaravelJaeger\Context\ContextArrayConverter\ContextArrayConverter;
use Jaeger\Codec\CodecInterface;
use Jaeger\Span\Context\SpanContext;

/**
 * Class JsonCodec
 * @package Ipunkt\LaravelJaeger\Codec
 */
class JsonCodec implements CodecInterface {
	/**
	 * @var ContextArrayConverter
	 */
	private $converter;

	/**
	 * JsonCodec constructor.
	 * @param ContextArrayConverter $converter
	 */
	public function __construct( ContextArrayConverter $converter) {
		$this->converter = $converter;
	}

	public function decode( $data ): ?SpanContext {
		return $this->converter
			->extract($data)
			->getContext();
	}

	public function encode( SpanContext $context ) {
		$data = [];

		$this->converter
			->setContext($context)
			->inject($data);

		return $data;
	}
}