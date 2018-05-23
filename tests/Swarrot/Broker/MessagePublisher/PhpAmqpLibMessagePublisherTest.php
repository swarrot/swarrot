<?php

namespace Swarrot\Tests\Broker\MessagePublisher;

use Prophecy\Argument;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Swarrot\Broker\Message;
use PhpAmqpLib\Channel\AMQPChannel;
use Swarrot\Broker\MessagePublisher\PhpAmqpLibMessagePublisher;

class PhpAmqpLibMessagePublisherTest extends TestCase
{
    public function test_publish_with_valid_message()
    {
        $channel = $this->prophesize(AMQPChannel::class);

        $channel->basic_publish(
            Argument::that(function (AMQPMessage $message) {
                $properties = $message->get_properties();

                return 'body' === $message->body && empty($properties);
            }),

            Argument::exact('swarrot'),
            Argument::exact('')
        )->shouldBeCalledTimes(1);

        $provider = new PhpAmqpLibMessagePublisher($channel->reveal(), 'swarrot');
        $return = $provider->publish(
            new Message('body')
        );

        $this->assertNull($return);
    }

    public function test_publish_with_application_headers()
    {
        $channel = $this->prophesize(AMQPChannel::class);

        $channel->basic_publish(
            Argument::that(function (AMQPMessage $message) {
                $properties = $message->get_properties();

                return
                    'body' === $message->body &&
                    $properties['application_headers']['int_header'] === ['I', 42] &&
                    $properties['application_headers']['string_header'] === ['S', 'my_value'] &&
                    $properties['application_headers']['array_header'] === ['A', ['foo' => 'bar']] &&
                    !isset($properties['headers']) &&
                    $message->serialize_properties()
                    ;
            }),
            Argument::exact('swarrot'),
            Argument::exact('')
        )->shouldBeCalledTimes(1);

        $provider = new PhpAmqpLibMessagePublisher($channel->reveal(), 'swarrot');
        $return = $provider->publish(
            new Message(
                'body',
                [
                    'headers' => [
                        'string_header' => 'my_value',
                        'array_header' => ['foo' => 'bar'],
                    ],
                    'application_headers' => [
                        'int_header' => ['I', 42],
                    ],
                ]
            )
        );

        $this->assertNull($return);
    }

    public function test_publish_with_publisher_confirms()
    {
        if (!method_exists(AMQPChannel::class, 'set_nack_handler')) {
            $this->markTestSkipped('The AMQP library version does not support publisher confirms');
        }
        $channel = $this->prophesize(AMQPChannel::class);
        $channel->set_nack_handler(
            Argument::type('\Closure')
        )->shouldBeCalledTimes(1);

        $channel->confirm_select()->shouldBeCalledTimes(1);

        $channel->basic_publish(
            Argument::that(function (AMQPMessage $message) {
                $properties = $message->get_properties();

                return 'body' === $message->body && empty($properties);
            }),

            Argument::exact('swarrot'),
            Argument::exact('')
        )->shouldBeCalledTimes(1);

        $channel->wait_for_pending_acks(
            Argument::exact(10)
        )->shouldBeCalledTimes(1);

        $provider = new PhpAmqpLibMessagePublisher($channel->reveal(), 'swarrot', true, 10);
        $return = $provider->publish(
            new Message('body')
        );

        $this->assertNull($return);
    }
}
