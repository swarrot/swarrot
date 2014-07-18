#!/bin/sh

set -e

echo "# Preparing vhost"
rabbitmqctl delete_vhost swarrot || true
rabbitmqctl add_vhost swarrot
rabbitmqctl set_permissions -p swarrot guest ".*" ".*" ".*"

echo "# Enable rabbitmq_management plugin"
rabbitmq-plugins enable rabbitmq_management

if ! type "rabbitmqadmin" > /dev/null
then
    echo "# Installing rabbitmqadmin"
    curl -XGET http://127.0.0.1:15672/cli/rabbitmqadmin > /usr/local/bin/rabbitmqadmin
    chmod +x /usr/local/bin/rabbitmqadmin
fi

echo "# Declaring mapping"
rabbitmqadmin declare exchange name=swarrot type=direct auto_delete=false durable=true --vhost=swarrot

rabbitmqadmin declare queue name=queue_with_messages auto_delete=false durable=true --vhost=swarrot
rabbitmqadmin declare queue name=empty_queue auto_delete=false durable=true --vhost=swarrot

rabbitmqadmin declare binding source=swarrot routing_key=test destination=queue_with_messages --vhost=swarrot

echo "# Create some messages"
for i in `seq 1 5`
do
    rabbitmqadmin publish routing_key="test" payload="message$i" exchange="swarrot" --vhost=swarrot
done
