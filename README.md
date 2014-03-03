# ExceptionCatcherProcessor

[![Build Status](https://travis-ci.org/swarrot/exception-catcher-processor.png)](https://travis-ci.org/swarrot/exception-catcher-processor)

ExceptionCatcherProcessor is a [swarrot](https://github.com/swarrot/swarrot) processor.
Is goal is to catch all exceptions to avoid stopping the worker.

## Installation

The recommended way to install ExceptionCatcherProcessor is through
[Composer](http://getcomposer.org/). Require the
`swarrot/instant-retry-processor` package into your `composer.json` file:

```json
{
    "require": {
        "swarrot/exception-catcher-processor": "@stable"
    }
}
```

**Protip:** you should browse the
[`swarrot/exception-catcher-processor`](https://packagist.org/packages/swarrot/exception-catcher-processor)
page to choose a stable version to use, avoid the `@stable` meta constraint.

## Usage

See [swarrot documentation](https://github.com/swarrot/swarrot).

## License

This project is released under the MIT License. See the bundled LICENSE file for details.
