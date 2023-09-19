<?php

namespace Swarrot\Broker\MessageProvider;

use Swarrot\Broker\Message;

class CallbackMessageProvider implements MessageProviderInterface
{
    private $get;
    private $ack;
    private $nack;

    public function __construct(callable $get, callable $ack = null, callable $nack = null)
    {
        $this->get = $get;
        $this->ack = $ack;
        $this->nack = $nack;
    }

    public function get(): ?Message
    {
        return \call_user_func($this->get);
    }

    public function ack(Message $message): void
    {
        if (null === $this->ack) {
            return;
        }

        \call_user_func($this->ack, $message);
    }

    public function nack(Message $message, bool $requeue = false): void
    {
        if (null === $this->nack) {
            return;
        }

        \call_user_func($this->nack, $message, $requeue);
    }

    public function getQueueName(): string
    {
        return '';
    }
}
