# AckProcessor

AckProcessor is a [swarrot](https://github.com/swarrot/swarrot) processor.
Its goal is to ack (or NACK) messages when needed.

## Configuration

|Key             |Default|Description                                            |
|:--------------:|:-----:|-------------------------------------------------------|
|requeue_on_error|false  |If true, the message will be requeued in the same queue|
