<?php

namespace Swarrot\Broker;

use PhpAmqpLib\Channel\AMQPChannel;

class PhpAmqpLibMessageProvider implements MessageProviderInterface
{
    /**
     * @var AMQPChannel
     */
    private $channel;

    /**
     * @var string
     */
    private $queueName;

    /**
     * @param AMQPChannel $channel
     * @param string      $queueName
     */
    public function __construct(AMQPChannel $channel, $queueName)
    {
        $this->channel   = $channel;
        $this->queueName = $queueName;
    }

    /**
     * {@inheritDoc}
     */
    public function get()
    {
        $envelope = $this->channel->basic_get($this->queueName);

        if (null === $envelope) {
            return null;
        }

        $headers = $envelope->has('application_headers') ? $envelope->get('application_headers') : array();

        return new Message($envelope->body, $headers, $envelope->get('delivery_tag'));
    }

    /**
     * {@inheritDoc}
     */
    public function ack(Message $message)
    {
        $this->channel->basic_ack($message->getId());
    }

    /**
     * {@inheritDoc}
     */
    public function nack(Message $message, $requeue = false)
    {
        $this->channel->basic_nack($message->getId(), false, $requeue);
    }

    /**
     * {@inheritDoc}
     */
    public function getQueueName()
    {
        return $this->queueName;
    }
}
