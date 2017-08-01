<?php

namespace Swarrot\Broker\MessageProvider;

use Interop\Queue\PsrConsumer;
use Interop\Queue\PsrContext;
use Interop\Queue\PsrMessage;
use Interop\Queue\PsrQueue;
use Swarrot\Broker\Message;

final class InteropMessageProvider implements MessageProviderInterface
{
    /**
     * @var PsrContext
     */
    private $context;

    /**
     * @var PsrConsumer
     */
    private $consumer;

    /**
     * @var PsrQueue
     */
    private $queue;

    /**
     * @var PsrMessage[]
     */
    private $consumedMessages = [];

    /**
     * @var float|int
     */
    private $waitTimeout;

    /**
     * @param PsrContext $context
     * @param string $queueName
     * @param float|int $waitTimeout
     */
    public function __construct(PsrContext $context, $queueName, $waitTimeout = 1000 /** 1sec */)
    {
        $this->context = $context;
        $this->waitTimeout = $waitTimeout;

        $this->queue = $context->createQueue($queueName);
        $this->consumer = $context->createConsumer($this->queue);
        $this->consumedMessages = [];
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        if (false == $message = $this->consumer->receive($this->waitTimeout)) {
            return;
        }

        $messageId = $message->getMessageId() ?: uniqid('', true);

        $this->consumedMessages[$messageId] = $message;

        $properties = $message->getHeaders();
        $properties['headers'] = $message->getProperties();

        return new Message($message->getBody(), $properties, $messageId);
    }

    /**
     * {@inheritdoc}
     */
    public function ack(Message $message)
    {
        if (false == isset($this->consumedMessages[$message->getId()])) {
            return;
        }

        $psrMessage = $this->consumedMessages[$message->getId()];
        unset($this->consumedMessages[$message->getId()]);

        $this->consumer->acknowledge($psrMessage);
    }

    /**
     * {@inheritdoc}
     */
    public function nack(Message $message, $requeue = false)
    {
        if (false == isset($this->consumedMessages[$message->getId()])) {
            return;
        }

        $psrMessage = $this->consumedMessages[$message->getId()];
        unset($this->consumedMessages[$message->getId()]);

        $this->consumer->reject($psrMessage, $requeue);
    }

    /**
     * {@inheritdoc}
     */
    public function getQueueName()
    {
        return $this->queue->getQueueName();
    }
}
