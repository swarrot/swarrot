<?php

require_once __DIR__.'/../vendor/autoload.php';

use Swarrot\Consumer;
use Swarrot\Broker\MessageProvider\PeclPackageMessageProvider;
use Swarrot\Broker\MessageInterface;
use Swarrot\Processor\ProcessorInterface;

class Processor implements ProcessorInterface
{
    public function process(MessageInterface $message, array $options)
    {
        printf("Consume message #%d\n", $message->getId());
    }
}

$connection = new \AMQPConnection();
$connection->connect();
$channel = new \AMQPChannel($connection);
$queue = new \AMQPQueue($channel);
$queue->setName('global');

$messageProvider = new PeclPackageMessageProvider($queue);

$consumer = new Consumer($messageProvider, new Processor());
$consumer->consume();
