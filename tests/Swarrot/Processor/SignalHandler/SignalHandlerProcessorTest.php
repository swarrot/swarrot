<?php

namespace Swarrot\Tests\Processor\SignalHandler;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;
use Psr\Log\LoggerInterface;
use Swarrot\Processor\SignalHandler\SignalHandlerProcessor;

class SignalHandlerProcessorTest extends TestCase
{
    public function test_it_is_initializable_without_a_logger()
    {
        $processor = $this->prophesize(ProcessorInterface::class);

        $processor = new SignalHandlerProcessor($processor->reveal());
        $this->assertInstanceOf(SignalHandlerProcessor::class, $processor);
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $processor = new SignalHandlerProcessor($processor->reveal(), $logger->reveal());
        $this->assertInstanceOf(SignalHandlerProcessor::class, $processor);
    }

    public function test_it_should_return_void_when_no_exception_is_thrown()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $message = new Message('body', array(), 1);
        $processor = new SignalHandlerProcessor($processor->reveal(), $logger->reveal());
        $this->assertNull($processor->process($message, array()));
    }

    public function test_it_should_throw_an_exception_after_consecutive_failed()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $message = new Message('body', array(), 1);

        $processor->process(
            Argument::exact($message),
            Argument::exact(array())
        )
        ->willThrow('\BadMethodCallException');

        $processor = new SignalHandlerProcessor($processor->reveal(), $logger->reveal());

        $this->expectException('\BadMethodCallException');
        $processor->process($message, array());
    }
}
