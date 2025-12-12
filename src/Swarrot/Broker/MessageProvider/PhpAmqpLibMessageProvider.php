<?php

namespace Swarrot\Broker\MessageProvider;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Wire\AMQPArray;
use Swarrot\Broker\Message;

/**
 * @final since 4.16.0
 */
class PhpAmqpLibMessageProvider implements MessageProviderInterface
{
    private AMQPChannel $channel;
    private string $queueName;

    public function __construct(AMQPChannel $channel, string $queueName)
    {
        $this->channel = $channel;
        $this->queueName = $queueName;
    }

    public function get(): ?Message
    {
        $envelope = $this->channel->basic_get($this->queueName);

        if (null === $envelope) {
            return null;
        }

        $properties = [];
        $propertyKeys = [
            'content_type', 'delivery_mode', 'content_encoding', 'type', 'timestamp', 'priority', 'expiration',
            'app_id', 'message_id', 'reply_to', 'correlation_id', 'user_id', 'cluster_id', 'channel', 'consumer_tag',
            'delivery_tag', 'redelivered', 'exchange', 'routing_key',
        ];

        foreach ($propertyKeys as $key) {
            if ($envelope->has($key)) {
                $properties[$key] = $envelope->get($key);
            }
        }

        $properties['headers'] = [];
        if ($envelope->has('application_headers')) {
            foreach ($envelope->get('application_headers') as $key => $value) {
                if ($value[1] instanceof AMQPArray) {
                    $properties['headers'][$key] = $value[1]->getNativeData();
                } else {
                    $properties['headers'][$key] = $value[1];
                }
            }
        }

        return new Message($envelope->getBody(), $properties, $envelope->get('delivery_tag'));
    }

    public function ack(Message $message): void
    {
        if (null === $id = $message->getId()) {
            throw new \RuntimeException('Cannot ack a message without id.');
        }

        $this->channel->basic_ack($id);
    }

    public function nack(Message $message, bool $requeue = false): void
    {
        if (null === $id = $message->getId()) {
            throw new \RuntimeException('Cannot nack a message without id.');
        }

        $this->channel->basic_nack($id, false, $requeue);
    }

    public function getQueueName(): string
    {
        return $this->queueName;
    }
}
