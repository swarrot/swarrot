# SignalHandlerProcessor

SignalHandlerProcessor is a [swarrot](https://github.com/swarrot/swarrot) processor.
Is goal is to handle signals to avoid the worker to stop during processing.

## Configuration

|Key                   |Default                        |Description                       |
|:--------------------:|:-----------------------------:|----------------------------------|
|signal_handler_signals|array(SIGTERM, SIGINT, SIGQUIT)|The list of all signals to handle.|
