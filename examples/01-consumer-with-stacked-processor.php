<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Swarrot\Consumer;
use Swarrot\Broker\MessageProvider\PeclPackageMessageProvider;
use Swarrot\Broker\Message;
use Swarrot\Processor\Decorator\DecoratorStackBuilder;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\Decorator\DecoratorInterface;

class Decorator implements DecoratorInterface
{
    protected $num;

    public function __construct($num = 1)
    {
        $this->num = (int) $num;
    }
    public function decorate(ProcessorInterface $processor, Message $message, array $options)
    {
        printf("Start processing message #%d in processor #%d\n", $message->getId(), $this->num);
        $return = $processor->process($message, $options);
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

$processor = (new DecoratorStackBuilder())
    ->addDecorator(new Decorator(42), 100)
    ->addDecorator(new Decorator(1))
    ->addDecorator(new Decorator(2))
    ->build(new FinalProcessor())
;

$consumer = new Consumer($messageProvider, $processor);
$consumer->consume();
