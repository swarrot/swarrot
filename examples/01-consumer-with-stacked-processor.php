<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Swarrot\Consumer;
use Swarrot\AMQP\PeclPackageMessageProvider;
use Swarrot\AMQP\Message;
use Swarrot\ParameterBag;
use Swarrot\Processor\ProcessorInterface;

class Processor implements ProcessorInterface {
    protected $processor;
    public function __construct($processor, $num = 1)
    {
        $this->processor = $processor;
        $this->num       = (int) $num;
    }
    public function __invoke(Message $message, ParameterBag $bag)
    {
        echo sprintf("Start processing message #%d in processor #%d\n", $message->getId(), $this->num);
        $return = call_user_func_array(
            $this->processor,
            array($message, $bag)
        );
        echo sprintf("End processing message #%d in processor #%d\n", $message->getId(), $this->num);

        return $return;
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
$processor = $stack->resolve(function(Message $message, ParameterBag $bag) {
    echo sprintf("Processing message #%d in callback.\n", $message->getId());
});

$consumer = new Consumer($messageProvider);
$consumer->consume($processor);
