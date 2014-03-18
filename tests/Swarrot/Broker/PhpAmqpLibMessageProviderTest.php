<?php

namespace Swarrot\Broker;

use PhpAmqpLib\Message\AMQPMessage;
use Prophecy\Prophet;

class PhpAmqpLibMessageProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Prophet
     */
    protected $prophet;

    protected function setUp()
    {
        $this->prophet = new Prophet;
    }

    protected function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function test_get_with_messages_in_queue_return_message()
    {
        $channel     = $this->prophet->prophesize('PhpAmqpLib\Channel\AMQPChannel');
        $amqpMessage = new AMQPMessage('foobar');

        $amqpMessage->delivery_info['delivery_tag'] =  '1';

        $channel->basic_get('my_queue')->shouldBeCalled()->willReturn($amqpMessage);

        $provider = new PhpAmqpLibMessageProvider($channel->reveal(), 'my_queue');
        $message  = $provider->get();

        $this->assertInstanceOf('Swarrot\Broker\Message', $message);
    }

    public function test_get_without_messages_in_queue_return_null()
    {
        $channel = $this->prophet->prophesize('PhpAmqpLib\Channel\AMQPChannel');

        $channel->basic_get('my_queue')->shouldBeCalled()->willReturn(null);

        $provider = new PhpAmqpLibMessageProvider($channel->reveal(), 'my_queue');
        $message  = $provider->get();

        $this->assertNull($message);
    }

    public function test_ack()
    {
        $channel = $this->prophet->prophesize('PhpAmqpLib\Channel\AMQPChannel');

        $channel->basic_ack('5')->shouldBeCalled();

        $provider = new PhpAmqpLibMessageProvider($channel->reveal(), 'my_queue');

        $provider->ack(new Message('5', 'foobar'));
    }

    public function test_nack()
    {
        $channel = $this->prophet->prophesize('PhpAmqpLib\Channel\AMQPChannel');

        $channel->basic_nack('5', false, true)->shouldBeCalled();

        $provider = new PhpAmqpLibMessageProvider($channel->reveal(), 'my_queue');

        $provider->nack(new Message('5', 'foobar'), true);
    }
}
