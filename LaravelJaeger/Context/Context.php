<?php namespace Ipunkt\LaravelJaeger\Context;

/**
 * Interface Context
 * @package Ipunkt\LaravelJaegerRabbitMQ\MessageContext
 */
interface Context
{
    function finish();

    function setPrivateTags(array $tags);

    function setPropagatedTags(array $tags);

    function log(array $fields);

    function inject(array &$messageData);

}