# SentryProcessor

SentryProcessor is a [swarrot](https://github.com/swarrot/swarrot) processor.
Its goal is to catch all exceptions to report them to Sentry.

The following tags are assigned to the event:

* `routing_key`
* `queue`

The message body is added as extra data in the event, the content is automatically cut by the Sentry client at 1024 characters.