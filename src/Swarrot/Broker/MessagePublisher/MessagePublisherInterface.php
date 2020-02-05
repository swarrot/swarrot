<?php

namespace Swarrot\Broker\MessagePublisher;

use Swarrot\Broker\Message;

interface MessagePublisherInterface
{
    public function publish(Message $message, string $key = null): void;

    /**
     * Return the name of the exchange where the message will be published.
     */
    public function getExchangeName(): string;
}
