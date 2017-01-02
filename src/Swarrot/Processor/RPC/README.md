# RPC Processors
The RPC processors are [swarrot](https://github.com/swarrot/swarrot)'s processors.
Their goal is to handle RPC communications between a RPC client and a RPC server.
Their usage is not mutually inclusive ; you can run one without the other.

`reply_to` message property contain the name of the queue on which the server will send the response, if exchange name is empty broker will receiver the message on the default exchange and route on the queue defined in the routing_key

## Configuration (Server processor)

*No need for specific configuration on server side*

## Configuration (Client processor)
| Key                         | Default   | Required ? | Description                                          |
| ---                         | -------   | ---------- | -----------                                          |
| `rpc_client_correlation_id` | *(empty)* | yes        | The message id the rpc client processor should watch |

## RabbitMQ tutorial example

[RabbitMQ example of RPC pattern](https://www.rabbitmq.com/tutorials/tutorial-six-python.html) 
![image](https://cloud.githubusercontent.com/assets/1516110/21552484/1f509970-ce02-11e6-949e-ad67510c76b7.png)


## Making it work, with a Fibonacci example
First of all, you'll need to declare an exchange (let's say a `rpc` exchange),
and a queue from which the server processor will consume from, with an appropriate
routing key. Let's call the queue `rpc_fibonacci`, and the routing key `fibonacci`.

Bind queue `rpc_fibonacci` to exchange `rpc` with routing key `fibonacci`.

On the server part, instantiate a new Consumer with a Server Processor.

On the client part :

- Create a temporary queue (i.e with an expiry ttl) and generate a `correlation_id`
- Send a message to the `rpc_fibonacci` queue (through the `rpc`
exchange) specifying your queue name as `reply_to` property and your correlation_id as `correlation_id` property
- Then launch a consumer with the client processor and your generated correlation_id as `rpc_client_correlation_id` option, in which you'll receive the response from the server
 
The client is waiting for the server to complete its designated task.

