<?php

namespace Swarrot\Broker\MessagePublisher;

use Swarrot\Broker\Message;

interface MessagePublisherInterface
{
    /**
     * publish
     *
     * @param Message $message The message to publish
     * @param string  $key     A routing key to use
     *
     * @return void
     */
    public function publish(Message $message, $key = null);
}
