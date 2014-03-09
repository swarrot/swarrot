<?php

namespace Swarrot\Processor\SignalHandler;

use Prophecy\Argument;
use Swarrot\Broker\Message;

class SignalHandlerProcessorTest extends \PHPUnit_Framework_TestCase
{
    protected $prophet;

    protected function setUp()
    {
        $this->prophet = new \Prophecy\Prophet;
    }

    protected function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function test_it_is_initializable_without_a_logger()
    {
        $processor       = $this->prophet->prophesize('Swarrot\Processor\ProcessorInterface');

        $processor = new SignalHandlerProcessor($processor->reveal());
        $this->assertInstanceOf('Swarrot\Processor\SignalHandler\SignalHandlerProcessor', $processor);
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $processor       = $this->prophet->prophesize('Swarrot\Processor\ProcessorInterface');
        $logger          = $this->prophet->prophesize('Psr\Log\LoggerInterface');

        $processor = new SignalHandlerProcessor($processor->reveal(), $logger->reveal());
        $this->assertInstanceOf('Swarrot\Processor\SignalHandler\SignalHandlerProcessor', $processor);
    }

    public function test_it_should_return_void_when_no_exception_is_thrown()
    {
        $processor       = $this->prophet->prophesize('Swarrot\Processor\ProcessorInterface');
        $logger          = $this->prophet->prophesize('Psr\Log\LoggerInterface');

        $message = new Message(1, 'body');
        $processor = new SignalHandlerProcessor($processor->reveal(), $logger->reveal());
        $this->assertNull($processor->process($message, array()));
    }

    public function test_it_should_throw_an_exception_after_consecutive_failed()
    {
        $processor       = $this->prophet->prophesize('Swarrot\Processor\ProcessorInterface');
        $logger          = $this->prophet->prophesize('Psr\Log\LoggerInterface');

        $message = new Message(1, 'body');

        $processor->process(
            Argument::exact($message),
            Argument::exact(array())
        )
        ->willThrow('\BadMethodCallException');

        $processor = new SignalHandlerProcessor($processor->reveal(), $logger->reveal());

        $this->setExpectedException('\BadMethodCallException');
        $processor->process($message, array());
    }
}
