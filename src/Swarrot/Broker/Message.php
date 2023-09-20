<?php

namespace Swarrot\Broker;

/**
 * @final since 4.16.0
 */
class Message
{
    private ?string $body;
    /**
     * Properties are similar to headers when using an \AMQPEnvelope object.
     */
    private array $properties;
    private ?string $id;

    /**
     * __construct.
     *
     * In AMQP 0.9.1, a message contains properties. One of these properties is
     * "headers".
     * In AMQP 1.0, a message contains both properties and headers.
     *
     * For example, RabbitMQ implement AMQP 0.9.1.
     * The "getHeaders" method of "\AMQPEnvelope" object actually return
     * message properties AND headers at the same level.
     * But if you want to have additional information, you have to put it in
     * the "headers" property. All unknown properties will be deleted by the
     * broker.
     *
     * More information on AMQP version:
     *
     * @see: http://www.amqp.org/resources/download
     */
    public function __construct(string $body = null, array $properties = [], string $id = null)
    {
        $this->body = $body;
        $this->properties = $properties;
        $this->id = $id;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getId(): ?string
    {
        return $this->id;
    }
}
