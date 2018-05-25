<?php

namespace Swarrot\Tests\Broker\MessageProvider;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPArray;
use PHPUnit\Framework\TestCase;
use Swarrot\Broker\Message;
use PhpAmqpLib\Channel\AMQPChannel;
use Swarrot\Broker\MessageProvider\PhpAmqpLibMessageProvider;

class PhpAmqpLibMessageProviderTest extends TestCase
{
    public function test_get_with_messages_in_queue_return_message()
    {
        $channel = $this->prophesize(AMQPChannel::class);
        $amqpMessage = new AMQPMessage('foobar');

        $amqpMessage->delivery_info['delivery_tag'] = '1';

        $channel->basic_get('my_queue')->shouldBeCalled()->willReturn($amqpMessage);

        $provider = new PhpAmqpLibMessageProvider($channel->reveal(), 'my_queue');
        $message = $provider->get();

        $this->assertInstanceOf('Swarrot\Broker\Message', $message);
    }

    public function test_get_with_amqp_array_header_return_array_header()
    {
        if (class_exists(AMQPArray::class)) {
            $channel = $this->prophesize(AMQPChannel::class);

            $properties = [
                "application_headers" => [
                    "x-death" => [
                        "0" => "S",
                        "1" => new AMQPArray(["data:protected" => "data"])
                    ]
                ]
            ];

            $amqpMessage = new AMQPMessage(
                'hello',
                $properties
            );

            $amqpMessage->delivery_info['delivery_tag'] = '1';

            $channel->basic_get('my_queue')->shouldBeCalled()->willReturn($amqpMessage);

            $provider = new PhpAmqpLibMessageProvider($channel->reveal(), 'my_queue');
            $message = $provider->get();

            $this->assertEquals(["0" => "data"], $message->getProperties()['headers']['x-death']);
        }
    }

    public function test_get_without_messages_in_queue_return_null()
    {
        $channel = $this->prophesize(AMQPChannel::class);

        $channel->basic_get('my_queue')->shouldBeCalled()->willReturn(null);

        $provider = new PhpAmqpLibMessageProvider($channel->reveal(), 'my_queue');
        $message = $provider->get();

        $this->assertNull($message);
    }

    public function test_ack()
    {
        $channel = $this->prophesize(AMQPChannel::class);

        $channel->basic_ack('5')->shouldBeCalled();

        $provider = new PhpAmqpLibMessageProvider($channel->reveal(), 'my_queue');

        $provider->ack(new Message('foobar', array(), 5));
    }

    public function test_nack()
    {
        $channel = $this->prophesize(AMQPChannel::class);

        $channel->basic_nack('5', false, true)->shouldBeCalled();

        $provider = new PhpAmqpLibMessageProvider($channel->reveal(), 'my_queue');

        $provider->nack(new Message('foobar', array(), 5), true);
    }

    public function test_get_name()
    {
        $channel = $this->prophesize(AMQPChannel::class);
        $provider = new PhpAmqpLibMessageProvider($channel->reveal(), 'foobar');

        $this->assertEquals('foobar', $provider->getQueueName());
    }
}
