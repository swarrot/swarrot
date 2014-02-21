<?php

namespace Swarrot\AMQP;

class Message
{
    protected $id;
    protected $body;
    protected $headers;

    public function __construct($id, $body, array $headers = array())
    {
        $this->id      = $id;
        $this->body    = $body;
        $this->headers = $headers;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getHeaders()
    {
        return $this->headers;
    }
}
