<?php

namespace Swarrot\Broker\MessagePublisher;

use Interop\Amqp\AmqpMessage;
use Interop\Queue\PsrContext;
use Interop\Queue\PsrProducer;
use Interop\Queue\PsrTopic;
use Swarrot\Broker\Message;

final class InteropMessagePublisher implements MessagePublisherInterface
{
    /**
     * @var PsrContext
     */
    private $context;

    /**
     * @var PsrProducer
     */
    private $producer;

    /**
     * @var PsrTopic
     */
    private $topic;

    /**
     * @param PsrContext $context
     * @param string     $topicName
     */
    public function __construct(PsrContext $context, $topicName)
    {
        $this->context = $context;

        $this->topic = $context->createTopic($topicName);
        $this->producer = $context->createProducer();
    }

    /** {@inheritdoc} */
    public function publish(Message $message, $key = null, callable $ackHandler = null, callable $nackHandler = null)
    {
        $headers = $message->getProperties();
        $properties = [];
        if (isset($headers['headers'])) {
            $properties = $headers['headers'];

            unset($headers['headers']);
        }

        $interopMessage = $this->context->createMessage($message->getBody(), $properties, $headers);

        if ($key && $interopMessage instanceof AmqpMessage) {
            $interopMessage->setRoutingKey($key);
        }

        $this->producer->send($this->topic, $interopMessage);
    }

    /**
     * {@inheritdoc}
     */
    public function getExchangeName()
    {
        return $this->topic->getTopicName();
    }
}
