<?php namespace Ipunkt\LaravelJaeger\Codec;

use Jaeger\Codec\TextCodec;
use Jaeger\Span\Context\SpanContext;

/**
 * Class ShortTextCodec
 * @package Ipunkt\LaravelJaeger\Codec
 */
class ShortTextCodec extends TextCodec {

	public function decode($data): ?SpanContext
	{
		$elements = collect(explode(':', $data));

		while( $elements->count() < 4 )
			$elements->push(0);

		$filledData = $elements->implode(':');

		return parent::decode($filledData);
	}
}