<?php

namespace Swarrot\Broker;

class PeclPackageMessageProvider implements MessageProviderInterface
{
    /**
     * @var \AMQPQueue
     */
    protected $queue;

    /**
     * @param \AMQPQueue $queue
     */
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

    /**
     * {@inheritDoc}
     */
    public function ack(Message $message)
    {
        $this->queue->ack($message->getId());
    }

    /**
     * {@inheritDoc}
     */
    public function nack(Message $message, $requeue = false)
    {
        $this->queue->nack($message->getId(), $requeue ? AMQP_REQUEUE : null);
    }

    /**
     * {@inheritDoc}
     */
    public function getQueueName()
    {
        return $this->queue->getName();
    }
}
