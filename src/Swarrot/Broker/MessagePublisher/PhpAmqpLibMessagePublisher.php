<?php

namespace Swarrot\Broker\MessagePublisher;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;
use Swarrot\Broker\Message;

class PhpAmqpLibMessagePublisher implements MessagePublisherInterface
{
    /** @var AMQPChannel $channel */
    private $channel;

    /** @var string $exchange Exchange's name. Required by php-amqplib */
    private $exchange;

    public function __construct(AMQPChannel $channel, $exchange)
    {
        $this->channel = $channel;
        $this->exchange = $exchange;
    }

    /** {@inheritdoc} */
    public function publish(Message $message, $key = null)
    {
        $properties = $message->getProperties();
        if (isset($properties['headers'])) {
            if (!isset($properties['application_headers'])) {
                $properties['application_headers'] = [];
            }
            foreach ($properties['headers'] as $header => $value) {
                if (is_array($value)) {
                    $type = 'A';
                } elseif (is_int($value)) {
                    $type = 'I';
                } else {
                    $type = 'S';
                }

                $properties['application_headers'][$header] = [$type, $value];
            }
        }

        $amqpMessage = new AMQPMessage($message->getBody(), $properties);

        $this->channel->basic_publish($amqpMessage, $this->exchange, (string) $key);
    }

    /**
     * {@inheritdoc}
     */
    public function getExchangeName()
    {
        return $this->exchange;
    }
}
