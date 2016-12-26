<?php

namespace Swarrot\Broker\MessageProvider;

use Swarrot\Broker\Message;

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

    /*
     * @var
     */
    protected $queueName;

    public function __construct(\Closure $get, $queueName = '', \Closure $ack = null, \Closure $nack = null)
    {
        $this->get = $get;
        $this->queueName = '';
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
    public function ack(Message $message)
    {
        if ($this->ack === null) {
            return;
        }

        return call_user_func($this->ack, $message);
    }

    /**
     * {@inheritdoc}
     */
    public function nack(Message $message, $requeue = false)
    {
        if ($this->nack === null) {
            return;
        }

        return call_user_func($this->nack, $message, $requeue);
    }

    /**
     * {@inheritdoc}
     */
    public function getQueueName()
    {
        return $this->queueName;
    }
}
