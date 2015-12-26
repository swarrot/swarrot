<?php

namespace Swarrot\Broker\MessagePublisher;

use Swarrot\Broker\Message;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class PeclPackageMessagePublisher implements MessagePublisherInterface
{
    protected $exchange;
    protected $flags;
    protected $logger;

    public function __construct(\AMQPExchange $exchange, $flags = AMQP_NOPARAM, LoggerInterface $logger = null)
    {
        $this->exchange = $exchange;
        $this->flags = $flags;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function publish(Message $message, $key = null)
    {
        $properties = $message->getProperties();
        if (isset($properties['application_headers'])) {
            if (!isset($properties['headers'])) {
                $properties['headers'] = [];
            }

            foreach ($properties['application_headers'] as $header => $value) {
                if (!is_array($value) || 2 !== count($value)) {
                    throw new \InvalidArgumentException(
                        'Unexpected value for application_headers "'.$header.'"'
                    );
                }

                $properties['headers'][$header] = $value[1];
            }

            unset($properties['application_headers']);
        }

        $body = $message->getBody();
        if (empty($body)) {
            $this->logger->notice('Publishing empty message.', [
                'message' => $body,
                'exchange' => $this->exchange->getName(),
                'key' => $key
            ]);
        }

        $this->exchange->publish(
            $message->getBody(),
            $key,
            $this->flags,
            $this->sanitizeProperties($properties)
        );
    }

    private function sanitizeProperties(array $properties)
    {
        if (isset($properties['headers'])) {
            $properties['headers'] = array_filter($properties['headers'], function ($headerValue) {
                return !is_array($headerValue);
            });
        }

        // workaround for https://github.com/pdezwart/php-amqp/issues/170. See https://github.com/swarrot/swarrot/issues/103
        if (isset($properties['delivery_mode']) && 0 === $properties['delivery_mode']) {
            unset($properties['delivery_mode']);
        }

        return $properties;
    }

    /**
     * {@inheritdoc}
     */
    public function getExchangeName()
    {
        return $this->exchange->getName();
    }
}
