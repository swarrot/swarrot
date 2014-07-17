<?php

namespace Swarrot\Broker\MessagePublisher;

use Prophecy\PhpUnit\ProphecyTestCase;
use Prophecy\Argument;
use PhpAmqpLib\Message\AMQPMessage;
use Swarrot\Broker\Message;

class PhpAmqpLibMessagePublisherTest extends ProphecyTestCase
{
    public function test_publish_with_valid_message()
    {
        $channel = $this->prophesize('PhpAmqpLib\Channel\AMQPChannel');

        $channel->basic_publish(
            Argument::that(function(AMQPMessage $message) {
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
}

