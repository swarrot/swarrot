<?php

namespace Swarrot\Processor\ExceptionCatcher;

use Swarrot\Processor\ExceptionCatcher\ExceptionCatcherProcessor;
use Prophecy\Argument;
use Swarrot\Broker\Message;

class ExceptionCatcherTest extends \PHPUnit_Framework_TestCase
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

        $processor = new ExceptionCatcherProcessor($processor->reveal());
        $this->assertInstanceOf('Swarrot\Processor\ExceptionCatcher\ExceptionCatcherProcessor', $processor);
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $processor       = $this->prophet->prophesize('Swarrot\Processor\ProcessorInterface');
        $logger          = $this->prophet->prophesize('Psr\Log\LoggerInterface');

        $processor = new ExceptionCatcherProcessor($processor->reveal(), $logger->reveal());
        $this->assertInstanceOf('Swarrot\Processor\ExceptionCatcher\ExceptionCatcherProcessor', $processor);
    }

    public function test_it_should_return_void_when_no_exception_is_thrown()
    {
        $processor       = $this->prophet->prophesize('Swarrot\Processor\ProcessorInterface');
        $logger          = $this->prophet->prophesize('Psr\Log\LoggerInterface');

        $message = new Message(1, 'body');
        $processor = new ExceptionCatcherProcessor($processor->reveal(), $logger->reveal());
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

        $processor = new ExceptionCatcherProcessor($processor->reveal(), $logger->reveal());

        $this->assertNull($processor->process($message, array()));
    }
}
