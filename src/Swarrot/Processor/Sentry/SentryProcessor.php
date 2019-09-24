<?php

namespace Swarrot\Processor\Sentry;

use Swarrot\Broker\MessageInterface;
use Swarrot\Processor\ProcessorInterface;

class SentryProcessor implements ProcessorInterface
{
    /**
     * @var ProcessorInterface
     */
    private $processor;

    /**
     * @var \Raven_Client
     */
    private $client;

    /**
     * @param ProcessorInterface $processor
     * @param \Raven_Client      $client
     */
    public function __construct(ProcessorInterface $processor, \Raven_Client $client)
    {
        $this->processor = $processor;
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, array $options)
    {
        try {
            return $this->processor->process($message, $options);
        } catch (\Throwable $e) {
            $this->handleException($e, $message, $options);
        }
    }

    /**
     * @param \Throwable       $exception
     * @param MessageInterface $message
     * @param array            $options
     *
     * @throws \Throwable
     */
    private function handleException(\Throwable $exception, MessageInterface $message, array $options)
    {
        $this->client->captureException($exception, $this->buildSentryData($message, $options));

        throw $exception;
    }

    /**
     * @param MessageInterface $message
     * @param array            $options
     *
     * @return array
     */
    protected function buildSentryData(MessageInterface $message, array $options)
    {
        $properties = $message->getProperties();

        $data = [
            'tags' => [
                'routing_key' => $properties['routing_key'] ?? '',
                'queue' => $options['queue'] ?? '',
            ],
            'extra' => [
                'message' => $message->getBody(),
            ],
        ];

        return $data;
    }
}
