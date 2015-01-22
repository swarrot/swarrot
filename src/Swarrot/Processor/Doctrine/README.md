# ConnectionProcessor

ConnectionProcessor is a [swarrot](https://github.com/swarrot/swarrot) processor.

The `doctrine_close_master` option is useful because otherwise once connected
to the master the connection would never switch back to the slave.

## Configuration

|Key                  |Default|Description                                            |
|:-------------------:|:-----:|-------------------------------------------------------|
|doctrine_ping        |true   |Ping and close timed out connections                   |
|doctrine_close_master|true   |Close MasterSlave connections connected to the master  |

# ObjectManagerProcessor

ObjectManagerProcessor is a [swarrot](https://github.com/swarrot/swarrot) processor.

It resets closed object managers after the processor ran, which is required
after a failed transaction.
It also clears the object managers which avoids memory leaks and hard to debug
issues.
