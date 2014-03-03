<?php

namespace spec\Swarrot\Processor;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Broker\MessageProviderInterface;
use Swarrot\Broker\Message;
use Psr\Log\LoggerInterface;

class ExceptionCatcherProcessorSpec extends ObjectBehavior
{
    protected $processor;
    protected $logger;

    function it_is_initializable_without_a_logger(ProcessorInterface $processor)
    {
        $this->beConstructedWith($processor);
        $this->shouldHaveType('Swarrot\Processor\ExceptionCatcherProcessor');
    }

    function it_is_initializable_with_a_logger(ProcessorInterface $processor, LoggerInterface $logger)
    {
        $this->beConstructedWith($processor, $logger);
        $this->shouldHaveType('Swarrot\Processor\ExceptionCatcherProcessor');
    }

    function it_should_return_void_when_no_exception_is_thrown(ProcessorInterface $processor, LoggerInterface $logger, Message $message)
    {
        $this->beConstructedWith($processor, $logger);
        $this->__invoke($message, array())->shouldReturn(null);
    }

    function it_should_throw_an_exception_after_consecutive_failed(ProcessorInterface $processor, LoggerInterface $logger, Message $message)
    {
        $processor->__invoke(
            Argument::type('Swarrot\Broker\Message'),
            Argument::exact(array())
        )
        ->willThrow('\Exception');

        $this->beConstructedWith($processor, $logger);
        $this->__invoke($message, array())->shouldReturn(null);
    }
}
