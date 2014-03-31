<?php

namespace Swarrot\Broker;

class Message
{
    /**
     * @var string
     */
    protected $body;

    /**
     * @var array
     */
    protected $headers;

    /**
     * @var int
     */
    protected $id;

    public function __construct($body, array $headers = array(), $id = null)
    {
        $this->body    = $body;
        $this->headers = $headers;
        $this->id      = $id;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getId()
    {
        return $this->id;
    }
}
