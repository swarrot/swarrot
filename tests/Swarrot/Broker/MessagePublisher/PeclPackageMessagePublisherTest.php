<?php

namespace Swarrot\Broker\MessagePublisher;

use Swarrot\Broker\Message;

class PeclPackageMessagePublisherTest extends \PHPUnit_Framework_TestCase
{
    public function test_publish_with_valid_message()
    {
        $provider = new PeclPackageMessagePublisher($this->getAMQPExchange());
        $return = $provider->publish(
            new Message('body')
        );

        $this->assertNull($return);
    }

    protected function getAMQPExchange()
    {
        $connection = new \AMQPConnection(array(
            'vhost' => 'swarrot'
        ));
        $connection->connect();
        $channel = new \AMQPChannel($connection);
        $exchange = new \AMQPExchange($channel);
        $exchange->setName('exchange');

        return $exchange;
    }
}
