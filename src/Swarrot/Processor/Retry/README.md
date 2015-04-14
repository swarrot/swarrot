# RetryProcessor

Retryprocessor is a [swarrot](https://github.com/swarrot/swarrot) processor.
Its goal is to re-published messages in broker when an error occurred.

## Configuration

|Key              |Default|Description                                                                   |
|:---------------:|:-----:|------------------------------------------------------------------------------|
|retry_key_pattern|       |[MANDATORY] The pattern to use to construct routing key (ie: `key_%attempt%`)|
|retry_attempts   |3      |The number of attempts before raising an exception.                           |

## How it works

When your processor throw an exception (= failed to process a message), the
Retry Processor will catch it, and republish the message in a given exchange
with the `retry_key_pattern`.

For example if the given `MessagePublisherInterface` is configured to publish
in the exchange named `retry` and if the `retry_key_pattern` is
`key_%attempts%`, when an exception is thrown, the `RetryProcessor` will
publish a new message (similar to the first one) in the exchange `retry` with
the routing_key `key_1`.

## Real example

Let's say you want to consume a queue named `mail`. You would like to have 3
retries if the mail is not sent correctly. The first retry 30 seconds after,
the second 3 minutes and the last one half an hour.

First, let's create the simple mail workflow.

* Create an exchange `mail` (type: `direct`)
* Create an queue `mail`
* Bind the exchange `mail` to the queue `mail` with routing_key `mail`

Now, to handle errors, let's say we republish all errors to an exchange
`retry`. For our mail, the `retry_key_pattern` will be `mail_retry_%attempts%`.
Here is what we need to have the first retry working:

* Create an exchange `retry` (type: `direct`)
* Create a queue `mail_retry_1` with following arguments: `x-message-ttl:
  30000` (our 30 seconds), `x-dead-letter-exchange: mail` (the first exchange
  we created), `x-dead-letter-routing-key: mail`
* Bind the exchange `retry` to the queue `mail_retry_1` with routing_key
  `mail_retry_1`

Once this configuration is done, when an exception is thrown, the
RetryProcessor will publish a new message (similar to the first one) to the
exchange `retry` with the routing_key `mail_retry_1`. This message will be
route to the queue `mail_retry_1`. As there is a ttl, 30 seconds after its
arrival, the message will be sent to the configured dead-letter exchange with
the given dead-letter routing-key. In our case the message will go into the
exchange mail with the routing key mail. Due to our first configuration, it
will finish in the queue mail which is consumed by our consumer.

Of course if we want more than only one retry, we can create new queues
`mail_retry_X` with the same configuration. The `RetryProcessor` will use the
`swarrot_retry_attempts` key in the message headers to determine if the message
must be retried or not.
