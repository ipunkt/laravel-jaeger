<?php namespace Ipunkt\LaravelJaeger\Context;

use Jaeger\Tracer\Tracer;

/**
 * Class MessageContext
 */
class MasterSpanContext extends SpanContext implements Context
{
    public function start()
    {
        $this->buildTracer();
    }

    public function finish()
    {
       parent::finish();
       $this->tracer->flush();
    }

    protected function buildTracer(): void
    {
        $this->tracer = app(Tracer::class);
    }
}
