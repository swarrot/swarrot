<?php

namespace Swarrot\Broker\MessagePublisher;

use Swarrot\Broker\Message;

interface MessagePublisherInterface
{
    /**
     * publish.
     *
     * @param Message  $message The message to publish
     * @param string   $key     A routing key to use
     * @param callable $ackHandler Called function on basic.ack receive
     * @param callable $nackHandler Called function on basic.nack receive
     */
    public function publish(Message $message, $key = null, callable $ackHandler = null, callable $nackHandler = null);

    /**
     * getExchangeName.
     *
     * Return the name of the exchange where the message will be published
     *
     * @return string
     */
    public function getExchangeName();
}
