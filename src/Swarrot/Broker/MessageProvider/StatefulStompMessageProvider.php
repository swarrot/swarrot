<?php

namespace Swarrot\Broker\MessageProvider;

use Stomp\Client;
use Stomp\StatefulStomp;
use Stomp\Transport\Message as StompMessage;
use Swarrot\Broker\Message;

class StatefulStompMessageProvider implements MessageProviderInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var StatefulStomp
     */
    private $stomp;

    /**
     * @var string
     */
    private $destination;

    /**
     * @param Client $client
     * @param string $destination
     * @param null   $selector
     * @param string $ack
     * @param array  $header
     */
    public function __construct(
        Client $client,
        $destination,
        $selector = null,
        $ack = 'client',
        array $header = []
    ) {
        $this->client = $client;
        $this->destination = $destination;

        $this->stomp = new StatefulStomp($client);
        $this->stomp->subscribe($destination, $selector, $ack, $header);
    }

    public function get()
    {
        if ($frame = $this->stomp->read()) {
            return new Message($frame->getBody(), $frame->getHeaders());
        }

        return null;
    }

    /**
     * @param Message $message
     */
    public function ack(Message $message)
    {
        $this->stomp->ack(new StompMessage($message->getBody(), $message->getProperties()));
    }

    /**
     * @param Message $message
     * @param bool    $requeue
     */
    public function nack(Message $message, $requeue = false)
    {
        $this->stomp->nack(new StompMessage($message->getBody(), $message->getProperties()), $requeue);
    }

    /**
     * @return string
     */
    public function getQueueName()
    {
        return $this->destination;
    }
}
