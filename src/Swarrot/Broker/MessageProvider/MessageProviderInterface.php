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
