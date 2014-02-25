<?php

namespace spec\Swarrot\Processor;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Broker\MessageProviderInterface;
use Swarrot\Broker\Message;
use Psr\Log\LoggerInterface;

class AckProcessorSpec extends ObjectBehavior
{
    protected $processor;
    protected $messageProvider;
    protected $logger;

    function let(ProcessorInterface $processor, MessageProviderInterface $messageProvider, LoggerInterface $logger)
    {
        $this->processor       = $processor;
        $this->messageProvider = $messageProvider;
        $this->logger          = $logger;
    }

    function it_is_initializable_without_a_logger()
    {
        $this->beConstructedWith($this->processor, $this->messageProvider);
        $this->shouldHaveType('Swarrot\Processor\AckProcessor');
    }

    function it_is_initializable_with_a_logger()
    {
        $this->beConstructedWith($this->processor, $this->messageProvider, $this->logger);
        $this->shouldHaveType('Swarrot\Processor\AckProcessor');
    }

    function it_should_ack_when_no_exception_is_thrown(Message $message)
    {
        $this->beConstructedWith($this->processor, $this->messageProvider, $this->logger);
        $this->__invoke($message, array())->shouldReturn(null);
    }

    function it_should_nack_when_an_exception_is_thrown(Message $message)
    {
        $this->processor->__invoke(
            Argument::exact($message),
            Argument::exact(array())
        )->willThrow(new \Exception());

        $this->beConstructedWith($this->processor, $this->messageProvider, $this->logger);
        $this->__invoke($message, array())->shouldReturn(null);
    }
}
