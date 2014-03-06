#!/bin/bash
#
# Using git-subsplit
# https://github.com/dflydev/git-subsplit

GIT_SUBSPLIT=$(pwd)/$(dirname $0)/git-subsplit.sh

$GIT_SUBSPLIT init https://github.com/swarrot/swarrot

$GIT_SUBSPLIT update

$GIT_SUBSPLIT publish "
    src/Swarrot/Processor/Ack:git@github.com:swarrot/ack-processor.git
    src/Swarrot/Processor/ExceptionCatcher:git@github.com:swarrot/exception-catcher-processor.git
    src/Swarrot/Processor/InstantRetry:git@github.com:swarrot/instant-retry-processor.git
    src/Swarrot/Processor/MaxExecutionTime:git@github.com:swarrot/max-execution-time-processor.git
    src/Swarrot/Processor/MaxMessages:git@github.com:swarrot/max-messages-processor.git
    src/Swarrot/Processor/SignalHandler:git@github.com:swarrot/signal-handler-processor.git
" --heads=master
