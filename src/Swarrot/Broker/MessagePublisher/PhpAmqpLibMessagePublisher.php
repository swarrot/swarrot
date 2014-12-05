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
        $this->channel  = $channel;
        $this->exchange = $exchange;
    }

    /** {@inheritDoc} */
    public function publish(Message $message, $key = null)
    {
        $properties = $message->getProperties();
        if (isset($properties['headers'])) {
            if (!isset($properties['application_headers'])) {
                $properties['application_headers'] = array();
            }
            foreach ($properties['headers'] as $header => $value) {
                if (is_array($value)) {
                    $type = 'A';
                } elseif(is_int($value)) {
                    $type = 'I';
                } else {
                    $type = 'S';
                }

                $properties['application_headers'][$header] = array($type, $value);
            }

        }

        $message = new AMQPMessage($message->getBody(), $properties);

        $this->channel->basic_publish($message, $this->exchange, (string) $key);
    }
}
