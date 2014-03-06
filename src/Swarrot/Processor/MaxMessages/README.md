# MaxMessagesProcessor

MaxMessagesProcessor is a [swarrot](https://github.com/swarrot/swarrot) processor.
The goal of this processor is to stop when X messages have been processed.

## Installation

The recommended way to install MaxMessagesProcessor is through
[Composer](http://getcomposer.org/). Require the `swarrot/max-messages-processor` package
into your `composer.json` file:

```json
{
    "require": {
        "swarrot/max-messages-processor": "@stable"
    }
}
```

**Protip:** you should browse the
[`swarrot/max-messages-processor`](https://packagist.org/packages/swarrot/max-messages-processor)
page to choose a stable version to use, avoid the `@stable` meta constraint.

## Usage

See [swarrot documentation](https://github.com/swarrot/swarrot).

## Configuration

|Key             |Default|Description                                               |
|:--------------:|:-----:|----------------------------------------------------------|
|max_messages    |100    |Set the maximum number of messages processed by the worker|

## License

MaxMessagesProcessor is released under the MIT License. See the bundled LICENSE file for details.
