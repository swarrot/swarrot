<?php

namespace Swarrot\Processor\RPC;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Swarrot\Broker\Message;
use Swarrot\Broker\MessagePublisher\MessagePublisherInterface;
use Swarrot\Processor\ProcessorInterface;

class RpcServerProcessorTest extends TestCase
{
    public function test_it_is_initializable_without_a_logger()
    {
        $processor        = $this->prophesize(ProcessorInterface::class);
        $messagePublisher = $this->prophesize(MessagePublisherInterface::class);

        $processor = new RpcServerProcessor($processor->reveal(), $messagePublisher->reveal());
        $this->assertInstanceOf(RpcServerProcessor::class, $processor);
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $processor        = $this->prophesize(ProcessorInterface::class);
        $messagePublisher = $this->prophesize(MessagePublisherInterface::class);
        $logger           = $this->prophesize(LoggerInterface::class);

        $processor = new RpcServerProcessor($processor->reveal(), $messagePublisher->reveal(), $logger->reveal());
        $this->assertInstanceOf(RpcServerProcessor::class, $processor);
    }

    /** @dataProvider noPropertiesProvider */
    public function test_it_should_do_an_early_return_if_no_adequate_properties(array $properties)
    {
        $processor        = $this->prophesize(ProcessorInterface::class);
        $messagePublisher = $this->prophesize(MessagePublisherInterface::class);
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

        $processor = $this->prophesize(ProcessorInterface::class);
        $processor->process($message, [])->willReturn('bar');

        $messagePublisher = $this->prophesize(MessagePublisherInterface::class);
        $messagePublisher->publish(Argument::that(function ($argument) {
            return $argument instanceof Message && 'bar' === $argument->getBody() && array_key_exists('correlation_id', $argument->getProperties());
        }), Argument::is('foo'))->shouldBeCalled();

        $processor = new RpcServerProcessor($processor->reveal(), $messagePublisher->reveal());

        $this->assertSame('bar', $processor->process($message, []));
    }
}

