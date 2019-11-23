<?php

namespace Swarrot\Tests\Processor\ExceptionCatcher;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Swarrot\Broker\Message;
use Swarrot\Processor\ExceptionCatcher\ExceptionCatcherProcessor;
use Swarrot\Processor\ProcessorInterface;

class ExceptionCatcherTest extends TestCase
{
    public function test_it_is_initializable_without_a_logger()
    {
        $processor = $this->prophesize(ProcessorInterface::class);

        $processor = new ExceptionCatcherProcessor($processor->reveal());
        $this->assertInstanceOf(ExceptionCatcherProcessor::class, $processor);
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $processor = new ExceptionCatcherProcessor($processor->reveal(), $logger->reveal());
        $this->assertInstanceOf(ExceptionCatcherProcessor::class, $processor);
    }

    public function test_it_should_return_void_when_no_exception_is_thrown()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $message = new Message('body', [], 1);
        $processor = new ExceptionCatcherProcessor($processor->reveal(), $logger->reveal());
        $this->assertNull($processor->process($message, []));
    }

    public function test_it_should_throw_an_exception_after_consecutive_failed()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $message = new Message('body', [], 1);

        $processor->process(
            Argument::exact($message),
            Argument::exact([])
        )
        ->willThrow('\BadMethodCallException');

        $processor = new ExceptionCatcherProcessor($processor->reveal(), $logger->reveal());

        $this->assertNull($processor->process($message, []));
    }
}
