<?php

namespace Swarrot\Processor\Decorator\ExceptionCatcher;

use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTestCase;
use Swarrot\Broker\Message;

class ExceptionCatcherDecoratorTest extends ProphecyTestCase
{
    public function test_it_is_initializable_without_a_logger()
    {
        $processor = new ExceptionCatcherDecorator();
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $logger = $this->prophesize('Psr\Log\LoggerInterface');

        $processor = new ExceptionCatcherDecorator($logger->reveal());
    }

    public function test_it_should_return_void_when_no_exception_is_thrown()
    {
        $decoratedProcessor = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $logger             = $this->prophesize('Psr\Log\LoggerInterface');

        $message = new Message('body', array(), 1);
        $processor = new ExceptionCatcherDecorator($logger->reveal());
        $this->assertNull($processor->decorate($decoratedProcessor->reveal(), $message, array()));
    }

    public function test_it_should_throw_an_exception_after_consecutive_failed()
    {
        $decoratedProcessor = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $logger             = $this->prophesize('Psr\Log\LoggerInterface');

        $message = new Message('body', array(), 1);

        $decoratedProcessor->process(
            Argument::exact($message),
            Argument::exact(array())
        )
        ->willThrow('\BadMethodCallException');

        $processor = new ExceptionCatcherDecorator($logger->reveal());

        $this->assertNull($processor->decorate($decoratedProcessor->reveal(), $message, array()));
    }
}
