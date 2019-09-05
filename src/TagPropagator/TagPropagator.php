<?php namespace Ipunkt\LaravelJaeger\TagPropagator;

use Jaeger\Span\SpanInterface;
use Jaeger\Tag\StringTag;

/**
 * Class TagPropagator
 * @package Ipunkt\LaravelJaegerRabbitMQ\TagPropagator
 */
class TagPropagator
{

    /**
     * @var array
     */
    protected $propagatedTags = [];

    protected $dataCarrierKey = 'propagated-tags';

    /**
     * @param $tags
     */
    public function addTags($tags)
    {
        $this->propagatedTags = array_merge($this->propagatedTags, $tags);
    }

    public function reset()
    {
        $this->propagatedTags = [];
    }

    /**
     * @param array $data
     */
    public function extract(array $data)
    {
        if(! array_key_exists($this->dataCarrierKey, $data) )
            return;

        $tagsFromData = $data[$this->dataCarrierKey];
        if( !is_array($tagsFromData) )
            return;

        $this->addTags( $tagsFromData );
    }

    /**
     * @param array $data
     */
    public function inject(array &$data)
    {
        $data[$this->dataCarrierKey] = $this->propagatedTags;
    }

    public function apply(SpanInterface $span)
    {
        foreach ($this->propagatedTags as $name => $value)
            $span->addTag( new StringTag($name, $value) );
    }

}
