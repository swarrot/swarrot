<?php

namespace Swarrot\Broker\MessagePublisher;

use Swarrot\Broker\Message;

class PeclPackageMessagePublisher implements MessagePublisherInterface
{
    protected $exchange;
    protected $flags;

    public function __construct(\AMQPExchange $exchange, $flags = AMQP_NOPARAM)
    {
        $this->exchange = $exchange;
        $this->flags    = $flags;
    }

    /**
     * {@inheritDoc}
     */
    public function publish(Message $message, $key = null)
    {
        $this->exchange->publish(
            $message->getBody(),
            $key,
            $this->flags,
            $message->getProperties()
        );
    }
}
