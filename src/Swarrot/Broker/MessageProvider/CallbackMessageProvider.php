<?php

namespace Swarrot\Broker\MessageProvider;

use Swarrot\Broker\Message;

class CallbackMessageProvider implements MessageProviderInterface
{
    /**
     * @var callable
     */
    protected $get;

    /**
     * @var callable
     */
    protected $ack;

    /**
     * @var callable
     */
    protected $nack;

    public function __construct(callable $get, callable $ack = null, callable $nack = null)
    {
        $this->get = $get;
        $this->ack = $ack;
        $this->nack = $nack;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return \call_user_func($this->get);
    }

    /**
     * {@inheritdoc}
     */
    public function ack(Message $message)
    {
        if (null === $this->ack) {
            return;
        }

        \call_user_func($this->ack, $message);
    }

    /**
     * {@inheritdoc}
     */
    public function nack(Message $message, $requeue = false)
    {
        if (null === $this->nack) {
            return;
        }

        \call_user_func($this->nack, $message, $requeue);
    }

    /**
     * {@inheritdoc}
     */
    public function getQueueName()
    {
        return '';
    }
}
