<?php

/**
 * In this example you will see how XDeathMaxCountProcessor work.
 *
 * Prerequis
 * First let's create the delaying exchange+queue
 *
 * Create an exchange `waiting_5` (type: `topic`)
 * Create an queue `waiting_5` (x-dead-letter-exchange: ``, x-message-ttl: `5000`)
 * Bind the exchange `waiting_5` to the queue `waiting_5` with routing_key `#`
 *
 * And then create the simple global queue.
 *
 * Create an queue `global` with (x-dead-letter-exchange: `waiting_5`, x-dead-letter-routing-key: `global`)
 *
 * Run this page and send manually a message
 */
require_once __DIR__.'/../vendor/autoload.php';

use Swarrot\Broker\Message;
use Swarrot\Broker\MessageProvider\PeclPackageMessageProvider;
use Swarrot\Consumer;
use Swarrot\Processor\ProcessorInterface;

class FailProcessor implements ProcessorInterface
{
    public function process(Message $message, array $options)
    {
        printf("Fail processor consume message #%d\n", $message->getId());

        throw new Exception('This is my process exception.');
    }
}

class PrintLogger extends Psr\Log\AbstractLogger
{
    public function log($level, $message, array $context = [])
    {
        printf("[Log %s] %s\n", $level, $message);
    }
}

$printLogger = new PrintLogger();

$connection = new AMQPConnection();
$connection->connect();
$channel = new AMQPChannel($connection);
$queue = new AMQPQueue($channel);
$queue->setName('global');

$messageProvider = new PeclPackageMessageProvider($queue);
$stack = (new Swarrot\Processor\Stack\Builder())
    ->push('\Swarrot\Processor\Ack\AckProcessor', $messageProvider, $printLogger)
    ->push(
        '\Swarrot\Processor\XDeath\XDeathMaxCountProcessor',
        'global',
        function ($e, $message, $options) {
            if (end($message->getProperties()['headers']['x-death'])['count'] > 5) {
                printf("XDeathMaxCountProcessor callback executed. Not rethrow original exception\n");

                // when you return false it not rethrow the catched exception
                return false;
            }

            printf("XDeathMaxCountProcessor callback executed. Rethrow original exception\n");

            // when you return null it rethrow the catched exception
            return;
        },
        $printLogger
    );

$consumer = new Consumer(
    $messageProvider,
    $stack->resolve(new FailProcessor()),
    null,
    $printLogger
);

echo '<pre>';
try {
    $consumer->consume([
        'x_death_max_count' => 3,
    ]);
} catch (Exception $exception) {
    printf("[%s] %s\n", get_class($exception), $exception->getMessage());
}
echo '</pre>';
