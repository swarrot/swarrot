<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Swarrot\Consumer;

$connection = new \AMQPConnection();
$connection->connect();
$channel = new \AMQPChannel($connection);
$queue = new \AMQPQueue($channel);
$queue->setName('global');

$consumer = new Consumer($queue);
$consumer->consume(function () {
    echo "consuming...\n";

    return true;
});
