<?php

namespace Swarrot\Broker;

class Message
{
    /**
     * @var string
     */
    protected $body;

    /**
     * Properties are similar to headers when using an \AMQPEnvelope object.
     *
     * @var array
     */
    protected $properties;

    /**
     * @var int
     */
    protected $id;

    public function __construct($body, array $properties = array(), $id = null)
    {
        $this->body       = $body;
        $this->properties = $properties;
        $this->id         = $id;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getHeaders()
    {
        trigger_error('getHeaders() method is deprecated. Use getProperties().', E_USER_DEPRECATED);

        return $this->getProperties();
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function getId()
    {
        return $this->id;
    }
}
