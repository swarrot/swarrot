<?php

namespace Swarrot\Tests\Processor\SignalHandler;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\SignalHandler\SignalHandlerProcessor;

class SignalHandlerProcessorTest extends TestCase
{
    use ProphecyTrait;

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

    public function test_it_should_return_true_when_no_exception_is_thrown()
    {
        $message = new Message('body', [], 1);

        $processor = $this->prophesize(ProcessorInterface::class);
        $processor->process($message, [])->shouldBeCalledTimes(1)->willReturn(true);

        $logger = $this->prophesize(LoggerInterface::class);

        $processor = new SignalHandlerProcessor($processor->reveal(), $logger->reveal());
        $this->assertTrue($processor->process($message, []));
    }

    public function test_it_should_throw_an_exception_after_consecutive_failed()
    {
        $message = new Message('body', [], 1);

        $processor = $this->prophesize(ProcessorInterface::class);
        $processor->process($message, [])->shouldBeCalledTimes(1)->willThrow(new \BadMethodCallException());

        $logger = $this->prophesize(LoggerInterface::class);

        $processor = new SignalHandlerProcessor($processor->reveal(), $logger->reveal());

        $this->expectException('\BadMethodCallException');
        $processor->process($message, []);
    }
}
