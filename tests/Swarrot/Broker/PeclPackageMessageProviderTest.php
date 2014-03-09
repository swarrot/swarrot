<?php

namespace Swarrot\Broker;

use Swarrot\Broker\PeclPackageMessageProvider;

class PeclPackageMessageProviderTest extends \PHPUnit_Framework_TestCase
{
    public function test_get_with_messages_in_queue_return_message()
    {
        $provider = new PeclPackageMessageProvider($this->getAMQPQueue('queue_with_messages'));
        $message = $provider->get();

        $this->assertInstanceOf('Swarrot\Broker\Message', $message);
    }

    public function test_get_without_messages_in_queue_return_null()
    {
        $provider = new PeclPackageMessageProvider($this->getAMQPQueue('empty_queue'));
        $message = $provider->get();

        $this->assertNull($message);
    }

    protected function getAMQPQueue($name)
    {
        $connection = new \AMQPConnection(array(
            'vhost' => 'swarrot'
        ));
        $connection->connect();
        $channel = new \AMQPChannel($connection);
        $queue = new \AMQPQueue($channel);
        $queue->setName($name);

        return $queue;
    }
}
