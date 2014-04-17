<?php

namespace Swarrot\Broker\MessagePublisher;

use Prophecy\Prophet;
use Prophecy\Argument;

use PhpAmqpLib\Message\AMQPMessage;

use Swarrot\Broker\MessagePublisher\PhpAmqpLibMessagePublisher;
use Swarrot\Broker\Message;

class PhpAmqpLibMessagePublisherTest extends \PHPUnit_Framework_TestCase
{
    /** @var Prophet */
    protected $prophet;

    public function setUp()
    {
        $this->prophet = new Prophet;
    }

    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function test_publish_with_valid_message()
    {
        $channel = $this->prophet->prophesize('PhpAmqpLib\Channel\AMQPChannel');

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

