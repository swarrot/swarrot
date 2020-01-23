<?php

namespace Swarrot\Broker\MessagePublisher;

use Stomp\Client;
use Stomp\SimpleStomp;
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
class SimpleStompMessagePublisher implements MessagePublisherInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var SimpleStomp
     */
    private $stomp;

    public function __construct(Client $client)
    {
        @trigger_error(sprintf('"%s" have been deprecated since Swarrot 3.7', __CLASS__), E_USER_DEPRECATED);

        $this->client = $client;
        $this->stomp = new SimpleStomp($client);
    }

    /**
     * @param null $key
     */
    public function publish(Message $message, $key = null)
    {
        $this->stomp->send($key, new StompMessage($message->getBody(), $message->getProperties()));
    }

    public function getExchangeName()
    {
        return;
    }
}
