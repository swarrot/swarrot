<?php

namespace Swarrot\Processor\Sentry;

use Swarrot\Broker\Message;
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

    public function __construct(ProcessorInterface $processor, \Raven_Client $client)
    {
        $this->processor = $processor;
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Message $message, array $options)
    {
        try {
            return $this->processor->process($message, $options);
        } catch (\Throwable $e) {
            $this->handleException($e, $message, $options);
        }
    }

    /**
     * @throws \Throwable
     */
    private function handleException(\Throwable $exception, Message $message, array $options)
    {
        $this->client->captureException($exception, $this->buildSentryData($message, $options));

        throw $exception;
    }

    /**
     * @return array
     */
    protected function buildSentryData(Message $message, array $options)
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
