# SignalHandlerProcessor

SignalHandlerProcessor is a [swarrot](https://github.com/swarrot/swarrot) processor.
Is goal is to handle signals to avoid the worker to stop during processing.

## Installation

The recommended way to install SignalHandlerProcessor is through 
[Composer](http://getcomposer.org/). Require the
`swarrot/signal-handler-processor` package into your `composer.json` file:

```json
{
    "require": {
        "swarrot/signal-handler-processor": "@stable"
    }
}
```

**Protip:** you should browse the
[`swarrot/signal-handler-processor`](https://packagist.org/packages/swarrot/signal-handler-processor)
page to choose a stable version to use, avoid the `@stable` meta constraint.

## Usage

See [swarrot documentation](https://github.com/swarrot/swarrot).

## Configuration

|Key                   |Default                        |Description                       |
|:--------------------:|:-----------------------------:|----------------------------------|
|signal_handler_signals|array(SIGTERM, SIGINT, SIGQUIT)|The list of all signals to handle.|

## License

This project is released under the MIT License. See the bundled LICENSE file for details.
