#!/bin/bash
#
# Using git-subsplit
# https://github.com/dflydev/git-subsplit

GIT_SUBSPLIT=$(pwd)/$(dirname $0)/git-subsplit.sh

$GIT_SUBSPLIT init https://github.com/swarrot/swarrot

$GIT_SUBSPLIT update

$GIT_SUBSPLIT publish "
    src/Swarrot/Processor/Ack:git@github.com:swarrot/ack-processor.git
    src/Swarrot/Processor/MaxExecutionTime:git@github.com:swarrot/max-execution-time-processor.git
" --heads=master
