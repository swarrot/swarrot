<?php

namespace Swarrot\Broker\MessageProvider;

use Swarrot\Broker\Message;

/**
 * @final since 4.16.0
 */
class PeclPackageMessageProvider implements MessageProviderInterface
{
    private \AMQPQueue $queue;

    public function __construct(\AMQPQueue $queue)
    {
        $this->queue = $queue;
    }

    public function get(): ?Message
    {
        try {
            $envelope = $this->queue->get();
        } catch (\AMQPConnectionException $exception) {
            $this->queue->getConnection()->reconnect();
            $envelope = $this->queue->get();
        }

        if (!$envelope instanceof \AMQPEnvelope) {
            return null;
        }

        return new Message(
            $envelope->getBody(),
            [
                'content_type' => $envelope->getContentType(),
                'routing_key' => $envelope->getRoutingKey(),
                'delivery_tag' => $envelope->getDeliveryTag(),
                'delivery_mode' => $envelope->getDeliveryMode(),
                'exchange_name' => $envelope->getExchangeName(),
                'is_redelivery' => $envelope->isRedelivery(),
                'content_encoding' => $envelope->getContentEncoding(),
                'type' => $envelope->getType(),
                'timestamp' => $envelope->getTimeStamp(),
                'priority' => $envelope->getPriority(),
                'expiration' => $envelope->getExpiration(),
                'app_id' => $envelope->getAppId(),
                'message_id' => $envelope->getMessageId(),
                'reply_to' => $envelope->getReplyTo(),
                'correlation_id' => $envelope->getCorrelationId(),
                'headers' => $envelope->getHeaders(),
                'user_id' => $envelope->getUserId(),
                'cluster_id' => 0,
                'channel' => '',
                'consumer_tag' => '',
            ],
            (string) $envelope->getDeliveryTag()
        );
    }

    public function ack(Message $message): void
    {
        if (null === $id = $message->getId()) {
            throw new \RuntimeException('Cannot ack a message without id.');
        }

        $this->queue->ack((int) $id);
    }

    public function nack(Message $message, bool $requeue = false): void
    {
        if (null === $id = $message->getId()) {
            throw new \RuntimeException('Cannot nack a message without id.');
        }

        $this->queue->nack((int) $id, $requeue ? \AMQP_REQUEUE : 0);
    }

    public function getQueueName(): string
    {
        return (string) $this->queue->getName();
    }
}
