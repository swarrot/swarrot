<?php

namespace Swarrot\Broker\MessageProvider;

use Swarrot\Broker\Message;

interface MessageProviderInterface
{
    /**
     * get.
     *
     * @return Message|null
     */
    public function get();

    /**
     * consume
     *
     * @param string $consumerTag Identify the consumer for the rabbitmq-server
     * @param callable $callback A callback receiving a Swarrot\Broker\Message instance on consumption
     */
    public function consume($consumerTag, callable $callback);

    /**
     * ack.
     *
     * @param Message $message
     */
    public function ack(Message $message);

    /**
     * nack.
     *
     * @param Message $message The message to NACK
     * @param bool    $requeue Requeue the message in the queue ?
     */
    public function nack(Message $message, $requeue = false);

    /**
     * getQueueName.
     *
     * @return string
     */
    public function getQueueName();
}
