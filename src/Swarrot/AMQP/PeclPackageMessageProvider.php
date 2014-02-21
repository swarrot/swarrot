<?php

namespace Swarrot\AMQP;

class PeclPackageMessageProvider implements MessageProviderInterface
{
    protected $queue;

    public function __construct(\AMQPQueue $queue)
    {
        $this->queue = $queue;
    }

    /**
     * {@inheritDoc}
     */
    public function get()
    {
        $envelope = $this->queue->get();
        if (!$envelope) {
            return null;
        }

        return new Message($envelope->getDeliveryTag(), $envelope->getBody(), $envelope->getHeaders());
    }
}
