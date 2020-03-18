<?php namespace Ipunkt\LaravelJaeger\Context;

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

    public function child($name): Context
    {
        return new self();
    }

	public function fromUberId( string $name, string $uberTraceId ) {
    	return $this;
	}
}
