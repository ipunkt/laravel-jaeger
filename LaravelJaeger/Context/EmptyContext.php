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

        $this->injectGlobaSpanData($messageData);
    }

    protected function injectGlobaSpanData(array &$messageData)
    {
        /**
         * @var Span $span
         */
        $span = app('context.tracer.globalSpan');

        /**
         * @var Jaeger $tracer
         */
        $tracer = app('context.tracer');
        $tracer->inject($span->getContext(), TEXT_MAP, $messageData);
    }

    public function parse(string $name, array $data)
    {
    }


}