# RetryProcessor

Retryprocessor is a [swarrot](https://github.com/swarrot/swarrot) processor.
Its goal is to re-published messages in broker when an error occurred.

## Installation

The recommended way to install Retryprocessor is through
[Composer](http://getcomposer.org/). Require the `swarrot/retry-processor`
package into your `composer.json` file:

```json
{
    "require": {
        "swarrot/retry-processor": "@stable"
    }
}
```

**Protip:** you should browse the
[`swarrot/retry-processor`](https://packagist.org/packages/swarrot/retry-processor)
page to choose a stable version to use, avoid the `@stable` meta constraint.

## Usage

See [swarrot documentation](https://github.com/swarrot/swarrot).

## Configuration

|Key             |Default|Description                                            |
|:--------------:|:-----:|-------------------------------------------------------|
|requeue_on_error|false  |If true, the message will be requeued in the same queue|

## License

This processor is released under the MIT License. See the bundled LICENSE file
for details.
