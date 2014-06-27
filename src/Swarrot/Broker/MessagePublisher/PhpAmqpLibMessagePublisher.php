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
        $message = new AMQPMessage($message->getBody(), $message->getProperties());

        $this->channel->basic_publish($message, $this->exchange, (string) $key);
    }
}
