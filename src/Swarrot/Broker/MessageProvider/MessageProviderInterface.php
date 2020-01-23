<?php

namespace Swarrot\Broker\MessageProvider;

use Swarrot\Broker\Message;

interface MessageProviderInterface
{
    public function get(): ?Message;

    public function ack(Message $message): void;

    public function nack(Message $message, bool $requeue = false): void;

    public function getQueueName(): string;
}
