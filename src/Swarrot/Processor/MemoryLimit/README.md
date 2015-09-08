# MemoryLimitProcessor

MemoryLimitProcessor is a [swarrot](https://github.com/swarrot/swarrot) processor.
The goal of this processor is to stop when the memory usages gets over a defined limit, to deal with processors leaking memory during the processing.

## Configuration

|Key             |Default|Description                                |
|:--------------:|:-----:|-------------------------------------------|
|memory_limit    |null   |Set the memory limit for the worker (in MB)|

``null`` means unlimited (until you reach the PHP memory limit triggering a fatal error of course)
