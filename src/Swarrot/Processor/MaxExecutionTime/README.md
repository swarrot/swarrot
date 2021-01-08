# MaxExecutionTimeProcessor

MaxExecutionTimeProcessor is a [swarrot](https://github.com/swarrot/swarrot)
processor.
Its goal is to stop the consumer when the max execution time have been reached.
That might result in redeliveries in some cases, for instance if you start
processing a message before the maximum execution time, then the next message
will be redelivered.
