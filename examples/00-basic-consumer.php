<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Swarrot\Consumer;
use Swarrot\Broker\PeclPackageMessageProvider;
use Swarrot\Broker\Message;

$connection = new \AMQPConnection();
$connection->connect();
$channel = new \AMQPChannel($connection);
$queue = new \AMQPQueue($channel);
$queue->setName('global');

$messageProvider = new PeclPackageMessageProvider($queue);

$consumer = new Consumer($messageProvider, function (Message $message) {
    echo sprintf("Consume message #%d\n", $message->getId());

    return true;
});
$consumer->consume();
