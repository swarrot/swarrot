# Change Log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

- Remove support for unmaintained Symfony versions. The min version is now 5.4
- Mark all classes as `@final` when they will become final in 5.0. Composition should be used instead of inheritance.

## 4.15.0 - 2023-09-12

- Improve MemoryLimitProcessor
- Add DBAL3 support on ConnectionProcessor

## 4.14.0 - 2023-01-03

- improve types for static analysis tools
- add testing on PHP 8.2

## 4.13.0 - 2022-10-19

- add support for psr/log 2 and 3
- fix support for doctrine/dbal 3 in the ConnectionProcessor

## [4.12.0] - 2021-12-09

- add Symfony 6.0 support
- bump minimum PHP support to 7.4

## [4.11.0] - 2021-01-21

There isn't anything new in this version.
A version 4.10 has been released instead of a 4.1.0 version.
That's why we go directly to the 4.11.0 version... :/

## [4.1.1] - 2021-01-21

- Fix MaxExecutionTimeProcessor processor

## [4.1.0] - 2020-12-16

- Add PHP 8 support

## [4.0.2] - 2020-12-08

- Improve doctrine support

## [4.0.1] - 2020-02-05

- Correct processor constructor: timeout can be a float

## [4.0.0] - 2020-02-05

- Remove all deprecated processors & message providers / publishers
- Add strict type hinting
- Make all class properties private by default

## [3.7.0] - 2020-01-29

- Add ServicesResetterProcessor
- Deprecate non boolean return from processors
- Deprecate Stomp message publishers
- Deprecate NewRelicProcessor
- Deprecate Interop message publishers & providers

## [3.6.1] - 2020-01-23

- Fix NewRelicProcessor

## [3.6.0] - 2020-01-22

- Deprecate Stomp message providers

## [3.5.0] - 2019-12-11

- Drop support of PHP < 7.2
- Add support for Symfony ^5.0
- Improve some logs
- Deprecate SQSMessageProvider
- Deprecate some processors: Sentry & RPC related processors

## [3.4.0] - 2019-05-19

- Always use static messages for logs
- Update minimal dependencies.
- Improve SignalHandlerProcessor with php7.1 pcntl_async_signals method.
- Improve RetryProcessor & retry_key configuration
- Implement Nack in SQS

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
