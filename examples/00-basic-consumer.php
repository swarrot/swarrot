<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Swarrot\Consumer;
use Swarrot\AMQP\PeclPackageMessageProvider;
use Swarrot\AMQP\Message;

$connection = new \AMQPConnection();
$connection->connect();
$channel = new \AMQPChannel($connection);
$queue = new \AMQPQueue($channel);
$queue->setName('global');

$messageProvider = new PeclPackageMessageProvider($queue);

$consumer = new Consumer($messageProvider);
$consumer->consume(function (Message $message, array $options) {
    echo sprintf("Consume message #%d\n", $message->getId());

    return true;
});
