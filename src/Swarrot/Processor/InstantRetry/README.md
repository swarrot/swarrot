# InstantRetryProcessor

InstantRetryProcessor is a [swarrot](https://github.com/swarrot/swarrot) processor.
Its goal is to recall the processor when an error occured.

## Configuration

|Key                   |Default|Description                                            |
|:--------------------:|:-----:|-------------------------------------------------------|
|instant_retry_delay   |2000000|The delay in micro seconds to wait before trying again.|
|instant_retry_attempts|3      |The number of attempts before raising an exception.    |
