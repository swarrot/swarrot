<?php

namespace Swarrot\Broker\MessageProvider;

use Interop\Queue\PsrConsumer;
use Interop\Queue\PsrContext;
use Interop\Queue\PsrMessage;
use Interop\Queue\PsrQueue;
use Swarrot\Broker\Message;

class InteropMessageProvider implements MessageProviderInterface
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
     * @param PsrContext $context
     * @param string $queueName
     */
    public function __construct(PsrContext $context, $queueName)
    {
        $this->context = $context;

        $this->queue = $context->createQueue($queueName);
        $this->consumer = $context->createConsumer($this->queue);
        $this->producer = $context->createProducer();

        $this->consumedMessages = [];
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        if (false == $message = $this->consumer->receiveNoWait()) {
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
        $this->consumer->acknowledge($this->consumedMessages[$message->getId()]);
    }

    /**
     * {@inheritdoc}
     */
    public function nack(Message $message, $requeue = false)
    {
        $this->consumer->reject($this->consumedMessages[$message->getId()], $requeue);
    }

    /**
     * {@inheritdoc}
     */
    public function getQueueName()
    {
        return $this->queue->getQueueName();
    }
}
