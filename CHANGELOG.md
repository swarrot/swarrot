# Change Log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

## [3.3.1] - 2018-10-11

- Correct release

## [3.3.0] - 2018-10-11

- Add XDeathMaxCountProcessor
- Add XDeathMaxLifetimeProcessor
- Deal with AMQPArray in PhpAmqpLibMessagePublisher
- Add stomp support

## [3.2.1] - 2017-12-25

- Make swarrot compatible with symfony4.

## [3.2.0] - 2017-10-31

- Add a sentry processor to capture exceptions

## [3.1.0] - 2017-08-01

- Add queue-interop message provider / publisher

## [3.0.0] - 2017-07-20

- Raise minimal PHP version to 7.1

## [2.4.0] - 2017-06-20

### Added

- Support publisher confirms.

## [2.3.0] - 2016-12-28

### Fixed

- Add missing exception in log context in RetryProcessor.

### Added

- New SQS provider.
- Allow custom log level bu thrown exception in Retry & InstantRetry processors.
- New Callback provider & processor (see `examples/02-consumer-and-provider-using-callbacks.php`).

## [2.2.0] - 2016-06-15

### Fixed

- Typos in errors messages, README, ...
- Deprecated warning with OptionsResolver > 2.8 in MemoryLimitProcessor.

### Added

- Catch of PHP7 Throwable.

## [2.1.2] - 2015-12-27

### Fixed

- `RetryProcessor` now keep previous headers when publishing a message.
- Fix coding standards.

### Added

- Support for sf3.
- Log when publihing empty message with `PeclPackageMessagePublisher`.

### Removed

- `phpspec/prophecy` is not needed anymore.

## [2.1.1] - 2015-09-08

### Changed

- Replace `OptionsResolverInterface` usage in `MemoryLimitProcessor`.

## [2.1.0] - 2015-09-08

### Added

- New `MemoryLimitProcessor`.
- New `NewRelicProcessor`.

## [2.0.3] - 2015-07-16

### Fixed

- Add a workaround for the bug in pecl amqp not exposing the delivery mode.
- Don't set default values for non-existing properties in PhpAmqpLibLMessageProvider.

## [2.0.2] - 2015-06-04

### Added

- Improve tests on Travis.
- Add missing processors in README.

### Fixed

- Compatibility with OptionsResolver <2.6.
- Correct typo in README.

### Changed

- Increase minimum required version for `doctrine/dbal` & `videlalvaro/php-amqplib`.

## [2.0.1] - 2015-06-03

### Fixed

- Replace deprecated `setAllowedTypes` calls.

## [2.0.0] - 2015-06-03

### Changed

- Use `OptionsResolver` instead of `OptionsResolverInterface`.

## [1.6.2] - 2015-04-18

## [1.6.1] - 2015-02-24

## [1.6.0] - 2015-01-06

## [1.5.0] - 2014-11-27

## [1.4.1] - 2014-11-01

## [1.4.0] - 2014-10-25

## [1.3.0] - 2014-07-17

## [1.2.8] - 2014-07-16

## [1.2.7] - 2014-07-15

## [1.2.6] - 2014-07-03

## [1.2.5] - 2014-07-02

## [1.2.4] - 2014-06-27

## [1.2.3] - 2014-06-19

## [1.2.2] - 2014-06-16

## [1.2.1] - 2014-05-13

## [1.2.0] - 2014-04-23

## [1.1.4] - 2014-04-02

## [1.1.3] - 2014-04-01

## [1.1.2] - 2014-04-01

## [1.1.1] - 2014-04-01

## [1.1.0] - 2014-04-01

[Unreleased]: https://github.com/swarrot/swarrot/compare/v3.3.1...HEAD
[3.3.1]: https://github.com/swarrot/swarrot/compare/v3.3.0...v3.3.1
[3.3.0]: https://github.com/swarrot/swarrot/compare/v3.2.1...v3.3.0
[3.2.1]: https://github.com/swarrot/swarrot/compare/v3.2.0...v3.2.1
[3.2.0]: https://github.com/swarrot/swarrot/compare/v3.1.0...v3.2.0
[3.1.0]: https://github.com/swarrot/swarrot/compare/v3.0.0...v3.1.0
[3.0.0]: https://github.com/swarrot/swarrot/compare/v2.4.0...v3.0.0
[2.4.0]: https://github.com/swarrot/swarrot/compare/v2.3.0...v2.4.0
[2.3.0]: https://github.com/swarrot/swarrot/compare/v2.2.0...v2.3.0
[2.2.0]: https://github.com/swarrot/swarrot/compare/v2.1.2...v2.2.0
[2.1.2]: https://github.com/swarrot/swarrot/compare/v2.1.1...v2.1.2
[2.1.1]: https://github.com/swarrot/swarrot/compare/v2.1.0...v2.1.1
[2.1.0]: https://github.com/swarrot/swarrot/compare/v2.0.3...v2.1.0
[2.0.3]: https://github.com/swarrot/swarrot/compare/v2.0.2...v2.0.3
[2.0.2]: https://github.com/swarrot/swarrot/compare/v2.0.1...v2.0.2
[2.0.1]: https://github.com/swarrot/swarrot/compare/v2.0.0...v2.0.1
[2.0.0]: https://github.com/swarrot/swarrot/compare/v1.6.2...v2.0.0
[1.6.2]: https://github.com/swarrot/swarrot/compare/v1.6.1...v1.6.2
[1.6.1]: https://github.com/swarrot/swarrot/compare/v1.6.0...v1.6.1
[1.6.0]: https://github.com/swarrot/swarrot/compare/v1.5.0...v1.6.0
[1.5.0]: https://github.com/swarrot/swarrot/compare/v1.4.1...v1.5.0
[1.4.1]: https://github.com/swarrot/swarrot/compare/v1.4.0...v1.4.1
[1.4.0]: https://github.com/swarrot/swarrot/compare/v1.3.0...v1.4.0
[1.3.0]: https://github.com/swarrot/swarrot/compare/v1.2.8...v1.3.0
[1.2.8]: https://github.com/swarrot/swarrot/compare/v1.2.7...v1.2.8
[1.2.7]: https://github.com/swarrot/swarrot/compare/v1.2.6...v1.2.7
[1.2.6]: https://github.com/swarrot/swarrot/compare/v1.2.5...v1.2.6
[1.2.5]: https://github.com/swarrot/swarrot/compare/v1.2.4...v1.2.5
[1.2.4]: https://github.com/swarrot/swarrot/compare/v1.2.3...v1.2.4
[1.2.3]: https://github.com/swarrot/swarrot/compare/v1.2.2...v1.2.3
[1.2.2]: https://github.com/swarrot/swarrot/compare/v1.2.1...v1.2.2
[1.2.1]: https://github.com/swarrot/swarrot/compare/v1.2.0...v1.2.1
[1.2.0]: https://github.com/swarrot/swarrot/compare/v1.1.4...v1.2.0
[1.1.4]: https://github.com/swarrot/swarrot/compare/v1.1.3...v1.1.4
[1.1.3]: https://github.com/swarrot/swarrot/compare/v1.1.2...v1.1.3
[1.1.2]: https://github.com/swarrot/swarrot/compare/v1.1.1...v1.1.2
[1.1.1]: https://github.com/swarrot/swarrot/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/swarrot/swarrot/compare/v1.0.0...v1.1.0
