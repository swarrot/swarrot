# RetryProcessor

Retryprocessor is a [swarrot](https://github.com/swarrot/swarrot) processor.
Its goal is to re-published messages in broker when an error occurred.

## Usage

See [swarrot documentation](https://github.com/swarrot/swarrot).

## Configuration

|Key              |Default|Description                                                                   |
|:---------------:|:-----:|------------------------------------------------------------------------------|
|retry_key_pattern|       |[MANDATORY] The pattern to use to construct routing key (ie: `key_%attempts%`)|
|retry_attempts   |3      |The number of attempts before raising an exception.                           |
