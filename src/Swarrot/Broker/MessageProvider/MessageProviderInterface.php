<?php

namespace Swarrot\Broker\MessageProvider;

use Swarrot\Broker\MessageInterface;

interface MessageProviderInterface
{
    /**
     * get.
     *
     * @return MessageInterface|null
     */
    public function get();

    /**
     * ack.
     *
     * @param MessageInterface $message
     */
    public function ack(MessageInterface $message);

    /**
     * nack.
     *
     * @param MessageInterface $message The message to NACK
     * @param bool    $requeue Requeue the message in the queue ?
     */
    public function nack(MessageInterface $message, $requeue = false);

    /**
     * getQueueName.
     *
     * @return string
     */
    public function getQueueName();
}
