<?php

namespace Swarrot\Broker\MessageProvider;

use Aws\Sqs\SqsClient;
use Swarrot\Broker\Message;
use Swarrot\Broker\MessageInterface;
use Swarrot\Driver\MessageCacheInterface;
use Swarrot\Driver\PrefetchMessageCache;

/**
 * Class SqsMessageProvider.
 */
class SqsMessageProvider implements MessageProviderInterface
{
    private $cache;
    private $channel;
    private $prefetch;
    private $waitTime;
    private $queueName;

    /**
     * @param SqsClient                  $channel
     * @param string                     $queueName
     * @param MessageCacheInterface|null $cache
     * @param int                        $prefetch
     * @param int                        $waitTime
     */
    public function __construct(
        SqsClient $channel,
        $queueName,
        MessageCacheInterface $cache = null,
        $prefetch = 9,
        $waitTime = 5
    ) {
        $this->channel = $channel;
        $this->queueName = $queueName;
        $this->cache = $cache ?: new PrefetchMessageCache();
        $this->prefetch = $prefetch;
        $this->waitTime = $waitTime;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        if ($message = $this->cache->pop($this->getQueueName())) {
            return $message;
        }

        $result = $this->channel->receiveMessage([
            'QueueUrl' => $this->getQueueName(),
            'MaxNumberOfMessages' => $this->prefetch,
            'WaitTimeSeconds' => $this->waitTime,
        ]);

        if (!$result || !$messages = $result->get('Messages')) {
            return null;
        }

        foreach ($messages as $message) {
            $attributes = array_key_exists('Attributes', $message) ? $message['Attributes'] : [];
            $swarrotMessage = new Message($message['Body'], $attributes, $message['ReceiptHandle']);
            $this->cache->push($this->getQueueName(), $swarrotMessage);
        }

        return $this->cache->pop($this->getQueueName());
    }

    /**
     * {@inheritdoc}
     */
    public function ack(MessageInterface $message)
    {
        $this->channel->deleteMessage([
            'QueueUrl' => $this->getQueueName(),
            'ReceiptHandle' => $message->getId(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function nack(MessageInterface $message, $requeue = false)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getQueueName()
    {
        return $this->queueName;
    }
}
