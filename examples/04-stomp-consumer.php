<?php
/**
 * See http://www.rabbitmq.com/stomp.html for more infos.
 * Run with rabbit stomp plugin.
 *
 * You must enable the plugin
 *   `rabbitmq-plugins enable rabbitmq_stomp`
 * You need to create rabbit user (stompUser, stompPass)
 *   Guest user have security restriction. See https://github.com/stomp-php/stomp-php/issues/105
 *
 * This example is the consumer part.
 * It print the message body
 * if the message body is "wrong" the message will be nack
 * else ack all message
 */
require_once __DIR__.'/../vendor/autoload.php';

use Swarrot\Broker\Message;
use Swarrot\Broker\MessageProvider\StatefulStompMessageProvider;
use Swarrot\Consumer;
use Swarrot\Processor\Callback\CallbackProcessor;

$client = new \Stomp\Client('tcp://localhost:61613');
$client->setLogin('stompUser', 'stompPass');
$client->setVhostname('/');
$client->connect();

$stompMessageProvider = new StatefulStompMessageProvider($client, '/queue/stomp_queue');

$processor = new CallbackProcessor(function (Message $message, array $options) use ($stompMessageProvider) {
    $body = $message->getBody();
    if ('wrong' == $body) {
        echo "$body NACK\r\n";
        $stompMessageProvider->nack($message);

        return;
    }
    echo "$body\r\n";
    $stompMessageProvider->ack($message);
});

$consumer = new Consumer($stompMessageProvider, $processor);

$consumer->consume([]);
