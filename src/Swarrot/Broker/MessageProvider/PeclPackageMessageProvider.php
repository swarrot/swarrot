<?php

namespace Swarrot\Broker\MessageProvider;

use Swarrot\Broker\Message;

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
     * {@inheritdoc}
     */
    public function consume($consumerTag, callable $callback)
    {

        $this->queue->consume(function(\AMQPEnvelope $envelope) use ($callback) {

            if (!$envelope) {
                throw new \Exception('No envelope');
            }

            return !$callback(new Message(
                $envelope->getBody(),
                array(
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
                ),
                $envelope->getDeliveryTag()
            ));

        }, AMQP_NOPARAM, $consumerTag);
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        $envelope = $this->queue->get();

        if (!$envelope) {
            return;
        }

        return new Message(
            $envelope->getBody(),
            array(
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
            ),
            $envelope->getDeliveryTag()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function ack(Message $message)
    {
        $this->queue->ack($message->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function nack(Message $message, $requeue = false)
    {
        $this->queue->nack($message->getId(), $requeue ? AMQP_REQUEUE : null);
    }

    /**
     * {@inheritdoc}
     */
    public function getQueueName()
    {
        return $this->queue->getName();
    }
}
