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
     * @param string $topicName
     */
    public function __construct(PsrContext $context, $topicName)
    {
        @trigger_error(sprintf('"%s" have been deprecated since Swarrot 3.7', __CLASS__), E_USER_DEPRECATED);

        $this->context = $context;

        $this->topic = $context->createTopic($topicName);
        $this->producer = $context->createProducer();
    }

    /** {@inheritdoc} */
    public function publish(Message $message, $key = null)
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
