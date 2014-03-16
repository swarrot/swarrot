<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Swarrot\Consumer;
use Swarrot\Broker\PeclPackageMessageProvider;
use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;

class Processor implements ProcessorInterface
{
    protected $processor;

    protected $num;

    public function __construct($processor, $num = 1)
    {
        $this->processor = $processor;
        $this->num       = (int) $num;
    }
    public function process(Message $message, array $options)
    {
        printf("Start processing message #%d in processor #%d\n", $message->getId(), $this->num);
        $return = $this->processor->process($message, $options);
        printf("End processing message #%d in processor #%d\n", $message->getId(), $this->num);

        return $return;
    }
}

class FinalProcessor implements ProcessorInterface
{
    public function process(Message $message, array $options)
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

$stack = (new \Swarrot\Processor\Stack\Builder())
    ->push('Processor', 1)
    ->push('Processor', 2)
;
$processor = $stack->resolve(new FinalProcessor());

$consumer = new Consumer($messageProvider, $processor);
$consumer->consume();
