# SignalHandlerProcessor

SignalHandlerProcessor is a [swarrot](https://github.com/swarrot/swarrot) processor.
The goal is to handle signals, in order to avoid worker to stop during processing.

## Configuration

|Key                   |Default                        |Description                       |
|:--------------------:|:-----------------------------:|----------------------------------|
|signal_handler_signals|array(SIGTERM, SIGINT, SIGQUIT)|The list of all signals to handle.|
