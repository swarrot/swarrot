<?php

namespace Swarrot\Broker\MessagePublisher;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Swarrot\Broker\Message;

/**
 * @final since 4.16.0
 */
class PhpAmqpLibMessagePublisher implements MessagePublisherInterface
{
    private AMQPChannel $channel;

    private string $exchange;

    /** @var int|float */
    private $timeout;

    private bool $publisherConfirms;

    /**
     * @param int|float $timeout
     */
    public function __construct(
        AMQPChannel $channel,
        string $exchange,
        bool $publisherConfirms = false,
        $timeout = 0
    ) {
        $this->channel = $channel;
        $this->exchange = $exchange;
        $this->publisherConfirms = $publisherConfirms;
        if ($publisherConfirms) {
            if (!method_exists($this->channel, 'set_nack_handler')) {
                throw new \Exception('Publisher confirms are not supported. Update your php amqplib package to >=2.2');
            }
            $this->channel->set_nack_handler($this->getNackHandler());
            $this->channel->confirm_select();
        }
        $this->timeout = $timeout;
    }

    public function publish(Message $message, ?string $key = null): void
    {
        $properties = $message->getProperties();
        if (isset($properties['headers'])) {
            if (!isset($properties['application_headers'])) {
                $properties['application_headers'] = [];
            }
            foreach ($properties['headers'] as $header => $value) {
                if (\is_array($value)) {
                    $type = 'A';
                } elseif (\is_int($value)) {
                    $type = 'I';
                } else {
                    $type = 'S';
                }

                $properties['application_headers'][$header] = [$type, $value];
            }
        }

        $amqpMessage = new AMQPMessage($message->getBody() ?? '', $properties);

        $this->channel->basic_publish($amqpMessage, $this->exchange, (string) $key);
        if ($this->publisherConfirms) {
            $this->channel->wait_for_pending_acks($this->timeout);
        }
    }

    public function getExchangeName(): string
    {
        return $this->exchange;
    }

    private function getNackHandler(): callable
    {
        return function (AMQPMessage $message) {
            if ($message->has('delivery_tag') && \is_scalar($message->get('delivery_tag'))) {
                throw new \Exception('Error publishing deliveryTag: '.$message->get('delivery_tag'));
            } else {
                throw new \Exception('Error publishing message: '.$message->getBody());
            }
        };
    }
}
