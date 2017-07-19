<?php

namespace Swarrot\Broker\MessagePublisher;

use Interop\Queue\PsrContext;
use Interop\Queue\PsrProducer;
use Interop\Queue\PsrTopic;
use Swarrot\Broker\Message;

class InteropMessagePublisher implements MessagePublisherInterface
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
     * @param string $topicName
     */
    public function __construct(PsrContext $context, $topicName)
    {
        $this->context = $context;

        $this->topic = $context->createTopic($topicName);
    }

    /** {@inheritdoc} */
    public function publish(Message $message, $key = null)
    {
        $headers = $message->getProperties();
        $properties = [];
        if (isset($headers['headers'])) {
            $properties =  $headers['headers'];

            unset($headers['headers']);
        }

        $this->producer->send(
            $this->topic,
            $this->context->createMessage($message->getBody(), $properties, $headers)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getExchangeName()
    {
        return $this->topic->getTopicName();
    }
}
