# InstantRetryProcessor

InstantRetryProcessor is a [swarrot](https://github.com/swarrot/swarrot) processor.
Its goal is to recall the processor when an error occurred.

## Configuration

|Key                         |Default|Description                                                   |
|:--------------------------:|:-----:|--------------------------------------------------------------|
|instant_retry_delay         |2000000|The delay in micro seconds to wait before trying again.       |
|instant_retry_attempts      |3      |The number of attempts before raising an exception.           |
|instant_retry_log_levels_map|[]     |Map of classes to a log level when retry. (Warning by default)|
