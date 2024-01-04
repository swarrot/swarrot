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
 * This example is the producer part.
 * It send a message with random body until it stop
 */
require_once __DIR__.'/../vendor/autoload.php';

use Swarrot\Broker\Message;
use Swarrot\Broker\MessagePublisher\StatefulStompMessagePublisher;

$client = new Stomp\Client('tcp://localhost:61613');
$client->setLogin('stompUser', 'stompPass');
$client->setVhostname('/');
$client->connect();
$stomp = new StatefulStompMessagePublisher($client);

while (true) {
    $items = ['good', 'nice', 'wrong'];
    $stomp->publish(new Message($items[array_rand($items)]), '/exchange/chat');
    sleep(1);
}
