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

    /**
     * @param AMQPChannel $channel
     * @param string $exchange
     */
    public function __construct(AMQPChannel $channel, $exchange)
    {
        $this->setChannel($channel);
        $this->setExchange($exchange);
    }

    /**
     * @param  AMQPChannel $channel
     * @return self
     */
    public function setChannel(AMQPChannel $channel)
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * @param  string $exchange
     * @return self
     */
    public function setExchange($exchange)
    {
        $this->exchange = $exchange;
        return $this;
    }

    /** {@inheritDoc} */
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
}
