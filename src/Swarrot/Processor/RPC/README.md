# RPC Processors
The RPC processors are [swarrot](https://github.com/swarrot/swarrot)'s processors.
Their goal is to handle RPC communications between a RPC client and a RPC server.
Their usage is not mutually inclusive ; you can run one without the other.

## Configuration (Server processor)
| Key                  | Default   | Required ? | Description                                       |
| ---                  | -------   | ---------- | -----------                                       |
| `rpc_server_message` | *(empty)* | no         | Message to send once the nested processor is done |

## Configuration (Client processor)
| Key                         | Default   | Required ? | Description                                          |
| ---                         | -------   | ---------- | -----------                                          |
| `rpc_client_correlation_id` | *(empty)* | yes        | The message id the rpc client processor should watch |

## Making it work, with a Fibonacci example
First of all, you'll need to declare an exchange (let's say a `rpc` exchange),
and a queue from which the server processor will consume from, with an appropriate
routing key. Let's call the queue `rpc_fibonacci`, and the routing key `fibonacci`.

On the server part, instantiate a new Consumer with a Server Processor ; on the
client part, send a message to the `rpc_fibonacci` queue (through the `rpc`
exchange), and then launch a consumer with the client processor, in which you'll
receive the response from the server ; the client is waiting for the server to 
complete its designated task.

