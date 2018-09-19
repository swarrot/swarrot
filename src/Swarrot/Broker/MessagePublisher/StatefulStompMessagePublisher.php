<?php

namespace Swarrot\Broker\MessagePublisher;

use Stomp\Client;
use Stomp\StatefulStomp;
use Stomp\Transport\Message as StompMessage;
use Swarrot\Broker\Message;

/**
 * See http://www.rabbitmq.com/stomp.html for more infos.
 *
 * When you publish you can set different key
 *
 * to publish in the exchange {EXCHANGE_NAME}
 *   '/exchange/{EXCHANGE_NAME}'
 * to publish in the exchange {EXCHANGE_NAME} with {ROUTING_KEY}
 *   '/exchange/{EXCHANGE_NAME}/{ROUTING_KEY}'
 * to publish in queue {QUEUE_NAME} (create the queue)
 *   '/queue/{QUEUE_NAME}'
 * to publish in queue {QUEUE_NAME} (that already exist in rabbit)
 *   '/amq/queue/{QUEUE_NAME}'
 * to publish in exchange amq.topic
 *   '/topic/{ROUTING_KEY}'
 *   '/exchange/amq.topic/{ROUTING_KEY}'
 *
 * You can also use 'temp-queue' refer to the doc
 */
class StatefulStompMessagePublisher implements MessagePublisherInterface
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
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->stomp = new StatefulStomp($client);
    }

    /**
     * {@inheritdoc}
     */
    public function publish(Message $message, $key = null, callable $ackHandler = null, callable $nackHandler = null)
    {
        $this->stomp->send($key, new StompMessage($message->getBody(), $message->getProperties()));
    }

    public function getExchangeName()
    {
        return;
    }
}
