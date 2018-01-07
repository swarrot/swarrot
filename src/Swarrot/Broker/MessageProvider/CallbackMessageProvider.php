<?php

namespace Swarrot\Broker\MessageProvider;

use Swarrot\Broker\MessageInterface;

class CallbackMessageProvider implements MessageProviderInterface
{
    /*
     * @var Closure
     */
    protected $get;

    /*
     * @var Closure
     */
    protected $ack;

    /*
     * @var Closure
     */
    protected $nack;

    public function __construct(\Closure $get, \Closure $ack = null, \Closure $nack = null)
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
        return call_user_func($this->get);
    }

    /**
     * {@inheritdoc}
     */
    public function ack(MessageInterface $message)
    {
        if (null === $this->ack) {
            return;
        }

        return call_user_func($this->ack, $message);
    }

    /**
     * {@inheritdoc}
     */
    public function nack(MessageInterface $message, $requeue = false)
    {
        if (null === $this->nack) {
            return;
        }

        return call_user_func($this->nack, $message, $requeue);
    }

    /**
     * {@inheritdoc}
     */
    public function getQueueName()
    {
        return '';
    }
}
