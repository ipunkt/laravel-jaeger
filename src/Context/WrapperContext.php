<?php namespace Ipunkt\LaravelJaeger\Context;

/**
 * Class WrapperContext
 * @package Ipunkt\LaravelJaeger\Context
 */
class WrapperContext implements Context
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Context
     */
    protected $parentContext;

    public function __construct(Context $context, Context $parentContext) {
        $this->context = $context;
        $this->parentContext = $parentContext;
    }

    public function finish()
    {
        $result = $this->context->finish();

        if(app('current-context') !== $this->context) {
            return;
        }

        app()->instance('current-context', $this->parentContext);

        return $result;
    }

    public function setPrivateTags(array $tags)
    {
        return $this->context->setPrivateTags($tags);
    }

    public function setPropagatedTags(array $tags)
    {
        return $this->context->setPropagatedTags($tags);
    }


    public function log( array $fields ) {
        return $this->context->log($fields);
    }

    public function inject(array &$messageData) {
        return $this->context->inject($messageData);
    }

    public function parse(string $name, array $data)
    {
        return $this->context->parse($name, $data);
    }

    public function child($name): Context
    {
        return $this->context->child($name);
    }

    public function __destruct()
    {
        $this->finish();
    }
}