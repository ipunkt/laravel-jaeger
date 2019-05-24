<?php namespace Ipunkt\LaravelJaeger\Context;

use Jaeger\Jaeger;
use const OpenTracing\Formats\TEXT_MAP;
use OpenTracing\Span;

/**
 * Class EmptyContext
 */
class EmptyContext implements Context
{

    public function finish()
    {

    }

    public function setPrivateTags(array $tags)
    {
    }

    public function setPropagatedTags(array $tags)
    {
    }


    public function log( array $fields ) {
    }

    public function inject(array &$messageData) {
    }

    public function parse(string $name, array $data)
    {
    }


}