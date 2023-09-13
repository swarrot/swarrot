# Swarrot

[![Build Status](https://travis-ci.org/swarrot/swarrot.png)](https://travis-ci.org/swarrot/swarrot)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/swarrot/swarrot/badges/quality-score.png?s=2c759b6224c762fc30a902d661b5512596060753)](https://scrutinizer-ci.com/g/swarrot/swarrot/)
[![Latest Stable Version](https://poser.pugx.org/swarrot/swarrot/v/stable.svg)](https://packagist.org/packages/swarrot/swarrot)
[![Latest Unstable Version](https://poser.pugx.org/swarrot/swarrot/v/unstable.svg)](https://packagist.org/packages/swarrot/swarrot)

Swarrot is a PHP library to consume messages from any broker.

## Installation

The recommended way to install Swarrot is through
[Composer](http://getcomposer.org/). Require the `swarrot/swarrot` package:

    $ composer require swarrot/swarrot

## Usage

### Basic usage

First, you need to create a message provider to retrieve messages from your
broker. For example, with a `PeclPackageMessageProvider` (retrieves messages from
an AMQP broker with the [pecl amqp package](http://pecl.php.net/package/amqp):

```php
use Swarrot\Broker\MessageProvider\PeclPackageMessageProvider;

// Create connection
$connection = new \AMQPConnection();
$connection->connect();
$channel = new \AMQPChannel($connection);
// Get the queue to consume
$queue = new \AMQPQueue($channel);
$queue->setName('global');

$messageProvider = new PeclPackageMessageProvider($queue);
```

Once it's done you need to create a `Processor` to process messages retrieved
from the broker. This processor must implement
`Swarrot\Processor\ProcessorInterface`. For example:

```php
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Broker\Message;

class Processor implements ProcessorInterface
{
    public function process(Message $message, array $options): bool
    {
        echo sprintf("Consume message #%d\n", $message->getId());

        return true; // Continue processing other messages
    }
}
```


You now have a `Swarrot\Broker\MessageProviderInterface` to retrieve messages
and a Processor to process them. So, ask the `Swarrot\Consumer` to do its job :

```php
use Swarrot\Consumer;

$consumer = new Consumer($messageProvider, $processor);
$consumer->consume();
```

### Using a stack

Heavily inspired by [stackphp/builder](https://github.com/stackphp/builder) you
can use `Swarrot\Processor\Stack\Builder` to stack your processors.
Using the [built in processors](#official-processors) or by [creating your
own](#create-your-own-processor), you can extend the behavior of your
base processor.
In this example, your processor is decorated by 2 other processors. The
[ExceptionCatcherProcessor](src/Swarrot/Processor/ExceptionCatcher/ExceptionCatcherProcessor.php)
which decorates your own with a try/catch block and the
[MaxMessagesProcessor](src/Swarrot/Processor/MaxMessages/MaxMessagesProcessor.php)
which stops your worker when some messages have been consumed.

```php
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Broker\Message;

class Processor implements ProcessorInterface
{
    public function process(Message $message, array $options): bool
    {
        echo sprintf("Consume message #%d\n", $message->getId());
        
        return true; // Continue processing other messages
    }
}

$stack = (new \Swarrot\Processor\Stack\Builder())
    ->push('Swarrot\Processor\MaxMessages\MaxMessagesProcessor', new Logger())
    ->push('Swarrot\Processor\ExceptionCatcher\ExceptionCatcherProcessor')
    ->push('Swarrot\Processor\Ack\AckProcessor', $messageProvider)
;

$processor = $stack->resolve(new Processor());
```

Here is an illustration to show you what happens when this order is used:

![this](https://docs.google.com/drawings/d/1Ea_QJHo-9p7YW8l_by7S4NID0e-AGpXRzzitAlYY5Cc/pub?w=960&h=720)

## Processors

### Official processors

* [AckProcessor](src/Swarrot/Processor/Ack)
* [Doctrine related processors](src/Swarrot/Processor/Doctrine) (thanks to [Adrien Brault](https://github.com/adrienbrault))
* [ExceptionCatcherProcessor](src/Swarrot/Processor/ExceptionCatcher)
* [InsomniacProcessor](src/Swarrot/Processor/Insomniac) (thanks to [Adrien Brault](https://github.com/adrienbrault))
* [InstantRetryProcessor](src/Swarrot/Processor/InstantRetry)
* [MaxExecutionTimeProcessor](src/Swarrot/Processor/MaxExecutionTime) (thanks to [Remy Lemeunier](https://github.com/remyLemeunier))
* [MaxMessagesProcessor](src/Swarrot/Processor/MaxMessages) (thanks to [Remy Lemeunier](https://github.com/remyLemeunier))
* [MemoryLimitProcessor](src/Swarrot/Processor/MemoryLimit) (thanks to [Christophe Coevoet](https://github.com/stof))
* [RetryProcessor](src/Swarrot/Processor/Retry)
* [ServicesResetterProcessor](src/Swarrot/Processor/ServicesResetter) (thanks to [Pierrick Vignand](https://github.com/pvgnd))
* [SignalHandlerProcessor](src/Swarrot/Processor/SignalHandler)
* [XDeathMaxCountProcessor](src/Swarrot/Processor/XDeath) (thanks to [Anthony Moutte](https://github.com/instabledesign))
* [XDeathMaxLifetimeProcessor](src/Swarrot/Processor/XDeath) (thanks to [Anthony Moutte](https://github.com/instabledesign))

### Create your own processor

To create your own processor and be able to use it with the `StackProcessor`, you
just need to implement `ProcessorInterface` and to take another
`ProcessorInterface` as first argument in constructor.

## Deprecated processors & message providers / publishers

In order to reduce `swarrot/swarrot` dependencies & ease the maintenance, some
processors & message providers / publishers have been deprecated in 3.x version.
They will be deleted in 4.0.

If you use those deprecated classes you could create your own repository to
keep them or we could create a dedicated repository under the swarrot
organisation if you're willing to help to maintain them.

### Message providers / publishers

* SQS Message provider (in 3.5.0)
* Stomp message providers (in 3.6.0)
* Stomp message publishers (in 3.7.0)
* Interop message publishers & providers (in 3.7.0)

### Processors

* SentryProcessor (in 3.5.0)
* RPC related processors (in 3.5.0)
* NewRelicProcessor (in 3.7.0)

## Inspiration

* [stackphp/builder](https://github.com/stackphp/builder)

## License

Swarrot is released under the MIT License. See the bundled LICENSE file for details.
