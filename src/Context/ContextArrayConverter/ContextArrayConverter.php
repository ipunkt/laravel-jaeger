<?php namespace Ipunkt\LaravelJaeger\Context\ContextArrayConverter;

use Jaeger\Span\Context\SpanContext;

/**
 * Class ContextArrayConverter
 */
class ContextArrayConverter
{

    /**
     * @var SpanContext
     */
    protected $context;

    public function extract($data)
    {
        $this->spanContext = new SpanContext(
            Arr::get($data, 'trace-id'),
            Arr::get($data, 'span-id'),
            Arr::get($data, 'parent-id'),
            Arr::get($data, 'flags'),
            Arr::get($data, 'baggage')
        );
        return $this;
    }

    /**
     * @return SpanContext
     */
    public function getContext(): SpanContext
    {
        return $this->context;
    }

    /**
     * @param SpanContext $context
     * @return ContextArrayConverter
     */
    public function setContext(SpanContext $context): ContextArrayConverter
    {
        $this->context = $context;
        return $this;
    }

    public function inject($data)
    {
        Arr::get($data, 'trace-id', $this->context->getTraceId());
        Arr::get($data, 'span-id', $this->context->getSpanId());
        Arr::get($data, 'parent-id', $this->context->getParentId());
        Arr::get($data, 'flags', $this->context->getFlags());
        Arr::get($data, 'baggage', $this->context->getBaggage());

        return $data;
    }
}