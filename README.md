# AckProcessor

[![Build Status](https://travis-ci.org/swarrot/swarrot.png)](https://travis-ci.org/swarrot/swarrot)

Swarrot is PHP library to consume messages from a broker.

## Installation

The recommended way to install Swarrot is through
[Composer](http://getcomposer.org/). Require the `swarrot/swarrot` package
into your `composer.json` file:

```json
{
    "require": {
        "swarrot/swarrot": "@stable"
    }
}
```

**Protip:** you should browse the
[`swarrot/swarrot`](https://packagist.org/packages/swarrot/swarrot)
page to choose a stable version to use, avoid the `@stable` meta constraint.

## Usage

### Basic usage

First, you need to create a message provider to retrieve message from you're
broker. For example, with A PeclPackageMessageProvider (retrieve message from
an AMQP broker with the [pecl amqp package](http://pecl.php.net/package/amqp):

    // Create connection
    $connection = new \AMQPConnection();
    $connection->connect();
    $channel = new \AMQPChannel($connection);
    // Get the queue to consume
    $queue = new \AMQPQueue($channel);
    $queue->setName('global');

    $messageProvider = new PeclPackageMessageProvider($queue);

Once it's done you need to create a Processor to process messages retrieved
from the broker. This processor can be a callback, a
`Swarrot\Processor\ProcessorInterface` or a
`Swarrot\Processor\Stack\StackedProcessor` (see [Using a
stack](#using-a-stack)). For the example, let's use a simple callback:

    $processor = function (Message $message, array $options) {
        echo sprintf("Consume message #%d\n", $message->getId());
    })

You now have a `Swarrot\Broker\MessageProviderInterface` to retrieve messages
and a Processor to process them. So, ask the `Swarrot\Consumer`to do it's job :

    $consumer = new Consumer($messageProvider, $processor);
    $consumer->consume();

### Using a stack


## Inspiration

* [stackphp/builder](https://github.com/stackphp/builder)

## License

Swarrot is released under the MIT License. See the bundled LICENSE file for details.
