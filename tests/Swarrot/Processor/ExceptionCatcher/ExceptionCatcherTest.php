<?php

namespace Swarrot\Processor\ExceptionCatcher;

use Prophecy\Argument;
use Swarrot\Broker\Message;

class ExceptionCatcherTest extends \PHPUnit_Framework_TestCase
{
    public function test_it_is_initializable_without_a_logger()
    {
        $processor       = $this->prophesize('Swarrot\Processor\ProcessorInterface');

        $processor = new ExceptionCatcherProcessor($processor->reveal());
        $this->assertInstanceOf('Swarrot\Processor\ExceptionCatcher\ExceptionCatcherProcessor', $processor);
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $processor       = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $logger          = $this->prophesize('Psr\Log\LoggerInterface');

        $processor = new ExceptionCatcherProcessor($processor->reveal(), $logger->reveal());
        $this->assertInstanceOf('Swarrot\Processor\ExceptionCatcher\ExceptionCatcherProcessor', $processor);
    }

    public function test_it_should_return_void_when_no_exception_is_thrown()
    {
        $processor       = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $logger          = $this->prophesize('Psr\Log\LoggerInterface');

        $message = new Message('body', array(), 1);
        $processor = new ExceptionCatcherProcessor($processor->reveal(), $logger->reveal());
        $this->assertNull($processor->process($message, array()));
    }

    public function test_it_should_throw_an_exception_after_consecutive_failed()
    {
        $processor       = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $logger          = $this->prophesize('Psr\Log\LoggerInterface');

        $message = new Message('body', array(), 1);

        $processor->process(
            Argument::exact($message),
            Argument::exact(array())
        )
        ->willThrow('\BadMethodCallException');

        $processor = new ExceptionCatcherProcessor($processor->reveal(), $logger->reveal());

        $this->assertNull($processor->process($message, array()));
    }
}
