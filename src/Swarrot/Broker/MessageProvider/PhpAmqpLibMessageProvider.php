<?php

namespace Swarrot\Broker\MessageProvider;

use PhpAmqpLib\Channel\AMQPChannel;
use Swarrot\Broker\Message;

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
            return;
        }

        $properties = [];
        $propertyKeys = [
            'content_type', 'delivery_mode', 'content_encoding', 'type', 'timestamp', 'priority', 'expiration',
            'app_id', 'message_id', 'reply_to', 'correlation_id', 'user_id', 'cluster_id', 'channel', 'consumer_tag',
            'delivery_tag', 'redelivered', 'exchange', 'routing_key'
        ];

        foreach($propertyKeys as $key) {
            if ($envelope->has($key)) {
                $properties[$key] = $envelope->get($key);
            }
        }

        $properties['headers'] = [];
        if ($envelope->has('application_headers')) {
            foreach ($envelope->get('application_headers') as $key => $value) {
                $properties['headers'][$key] = $value[1];
            }
        }

        return new Message($envelope->body, $properties, $envelope->get('delivery_tag'));
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
