# XDeathMaxCountProcessor

XDeathMaxCountProcessor is a [swarrot](https://github.com/swarrot/swarrot) processor.
Its goal is to execute a callback when x-death max count reached in order to kill poison message.

This is a very different approach than RetryProcessor because there is no software clone message republish.
This processor use the native rabbitmq x-death functionnality.

## Configuration

|Key                                  |Default|Description                                                                  |
|:-----------------------------------:|:-----:|-----------------------------------------------------------------------------|
|x_death_max_count                    |300    |The number of attempts before callback.
|x_death_max_count_log_levels_map     |[]     |Map of classes to a log level when retry. (Warning by default)               |
|x_death_max_count_fail_log_levels_map|[]     |Map of classes to a log level when all retries failed. (Warning by default)  |

## How it works

When your processor throw an exception (= failed to process a message), the
XDeathMaxCount Processor will catch it and execute the callback if the message header x-death count
exceed the `x_death_max_count`.

Your callback return a boolean or null:
* return true to continue to process messages
* return false to stop the consumer
* return null to throw an exception

## Real example

Let's say you want to consume a queue named `mail`. You would like to have 3
retries if the mail is not sent correctly. Each retry was separate by 30 seconds.

First let's create the delaying exchange+queue

* Create an exchange `waiting_30` (type: `topic`)
* Create an queue `waiting_30` (x-dead-letter-exchange: ` `, x-message-ttl: `30000`)
* Bind the exchange `waiting_30` to the queue `waiting_30` with routing_key `#`

And then create the simple mail workflow.

* Create an exchange `mail` (type: `direct`)
* Create an queue `queue_mail` with (x-dead-letter-exchange: `waiting_30`, x-dead-letter-routing-key: `queue_mail`)
* Bind the exchange `mail` to the queue `queue_mail` with routing_key `queue_mail`

You must use the AckProcessor (or nack the message by yourself).

Once this configuration is done, when an exception is thrown, the
AckProcessor will nack the message and rabbitmq will append or increase message header
x-death count. This x-death header is the message trace when the message was `reject` or `expired` by a queue.
So the message was reject to exchange `waiting_30` and route to queue `waiting_30`.
After 30 seconds the queue `expire` the message and move it to AMQP default exchange
and route directly to queue `queue_mail`.
If your consumer throw exception 3 times then the XDeathMaxCount will execute the configured callback
and not rethrow the exception in order to let the Ack processor ack the message to stop retrying it.


# XDeathMaxLifetimeProcessor

XDeathMaxLifetimeProcessor is a [swarrot](https://github.com/swarrot/swarrot) processor.
Its goal is to execute a callback when x-death time exceed max lifetime in order to kill poison message.

This is a very different approach than RetryProcessor because there is no software clone message republish.
This processor use the native rabbitmq x-death functionnality.

## Configuration

|Key                                     |Default|Description                                                                  |
|:--------------------------------------:|:-----:|-----------------------------------------------------------------------------|
|x_death_max_lifetime                    |3600   |The number seconds before callback.
|x_death_max_lifetime_log_levels_map     |[]     |Map of classes to a log level when retry. (Warning by default)               |
|x_death_max_lifetime_fail_log_levels_map|[]     |Map of classes to a log level when all retries failed. (Warning by default)  |

## How it works

When your processor throw an exception (= failed to process a message), the
XDeathMaxLifetime Processor will catch it and execute the callback if the message header x-death time
exceed the `x_death_max_lifetime`.

Your callback return a boolean or null:
* return true to continue to process messages
* return false to stop the consumer
* return null to throw an exception

## Real example

Let's say you want to consume a queue named `mail`. You would like to
retry during 1 hour if the mail is not sent correctly. Each retry was separate by 30 seconds.

First let's create the delaying exchange+queue

* Create an exchange `waiting_30` (type: `topic`)
* Create an queue `waiting_30` (x-dead-letter-exchange: ` `, x-message-ttl: `30000`)

And then create the simple mail workflow.

* Create an exchange `mail` (type: `direct`)
* Create an queue `queue_mail` with (x-dead-letter-exchange: `waiting_30`, x-dead-letter-routing-key: `queue_mail`)
* Bind the exchange `mail` to the queue `queue_mail` with routing_key `queue_mail`

You must use the AckProcessor (or nack the message by yourself).

Once this configuration is done, when an exception is thrown, the
AckProcessor will nack the message and rabbitmq will append x-death time header (with timestamp).
This x-death header is the message trace when the message was `reject` or `expired` by a queue.
So the message was reject to exchange `waiting_30` and route to queue `waiting_30`.
After 30 seconds the queue `expire` the message and move it to AMQP default exchange
and route directly to queue `queue_mail`.
After 1 hour if your consumer throw exception then the XDeathMaxLifetime will execute the configured callback
and not rethrow the exception in order to let the Ack processor ack the message to stop retrying it.
