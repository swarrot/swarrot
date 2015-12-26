<?php

namespace Swarrot\Processor\RPC;

use Prophecy\Argument;
use Swarrot\Broker\Message;

class RpcServerProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function test_it_is_initializable_without_a_logger()
    {
        $processor        = $this->prophesize('Swarrot\\Processor\\ProcessorInterface');
        $messagePublisher = $this->prophesize('Swarrot\\Broker\\MessagePublisher\\MessagePublisherInterface');

        $processor = new RpcServerProcessor($processor->reveal(), $messagePublisher->reveal());
        $this->assertInstanceOf('Swarrot\\Processor\\RPC\\RpcServerProcessor', $processor);
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $processor        = $this->prophesize('Swarrot\\Processor\\ProcessorInterface');
        $messagePublisher = $this->prophesize('Swarrot\\Broker\\MessagePublisher\\MessagePublisherInterface');
        $logger           = $this->prophesize('Psr\\Log\\LoggerInterface');

        $processor = new RpcServerProcessor($processor->reveal(), $messagePublisher->reveal(), $logger->reveal());
        $this->assertInstanceOf('Swarrot\\Processor\\RPC\\RpcServerProcessor', $processor);
    }

    /** @dataProvider noPropertiesProvider */
    public function test_it_should_do_an_early_return_if_no_adequate_properties(array $properties)
    {
        $processor        = $this->prophesize('Swarrot\\Processor\\ProcessorInterface');
        $messagePublisher = $this->prophesize('Swarrot\\Broker\\MessagePublisher\\MessagePublisherInterface');
        $messagePublisher->publish()->shouldNotBeCalled();

        $processor = new RpcServerProcessor($processor->reveal(), $messagePublisher->reveal());

        $message = new Message('', $properties);
        $this->assertNull($processor->process($message, []));
    }

    public function noPropertiesProvider()
    {
        return [[[]],
                [['reply_to' => 'foo']],
                [['correlation_id' => 0]],
                [['reply_to' => '', 'correlation_id' => 0]],
                [['reply_to' => '', 'correlation_id' => 42]],
                [['reply_to' => 'foo', 'correlation_id' => 0]]];
    }

    public function test_it_should_publish_a_new_message_when_done()
    {
        $message = new Message('', ['reply_to' => 'foo', 'correlation_id' => 42]);

        $processor = $this->prophesize('Swarrot\\Processor\\ProcessorInterface');
        $processor->process($message, [])->willReturn('bar');

        $messagePublisher = $this->prophesize('Swarrot\\Broker\\MessagePublisher\\MessagePublisherInterface');
        $messagePublisher->publish(Argument::that(function ($argument) {
            return $argument instanceof Message && 'bar' === $argument->getBody() && array_key_exists('correlation_id', $argument->getProperties());
        }), Argument::is('foo'))->shouldBeCalled();

        $processor = new RpcServerProcessor($processor->reveal(), $messagePublisher->reveal());

        $this->assertSame('bar', $processor->process($message, []));
    }
}

