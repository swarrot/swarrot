<?php

namespace spec\Swarrot\Processor;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Broker\MessageProviderInterface;
use Swarrot\Broker\Message;
use Psr\Log\LoggerInterface;

class InstantRetryProcessorSpec extends ObjectBehavior
{
    protected $processor;
    protected $logger;

    function let(ProcessorInterface $processor, LoggerInterface $logger)
    {
        $this->processor = $processor;
        $this->logger    = $logger;
    }

    function it_is_initializable_without_a_logger()
    {
        $this->beConstructedWith($this->processor);
        $this->shouldHaveType('Swarrot\Processor\InstantRetryProcessor');
    }

    function it_is_initializable_with_a_logger()
    {
        $this->beConstructedWith($this->processor, $this->logger);
        $this->shouldHaveType('Swarrot\Processor\InstantRetryProcessor');
    }

    function it_should_return_void_when_no_exception_is_thrown(Message $message)
    {
        $this->beConstructedWith($this->processor, $this->logger);
        $this->__invoke($message, array(
            'instant_retry_attempts' => 3,
            'instant_retry_delay' => 1000
        ))->shouldReturn(null);
    }

    function it_should_throw_an_exception_after_consecutive_failed(Message $message)
    {
        $exception = new \Exception('toto');

        $this->processor->__call('__invoke', array(
            Argument::type('Swarrot\Broker\Message'),
            Argument::exact(array(
                'instant_retry_attempts' => 3,
                'instant_retry_delay' => 1000
            ))
        ))
        ->shouldBeCalledTimes(3)
        ->willThrow('\Exception');

        $this->beConstructedWith($this->processor, $this->logger);
        $this->shouldThrow('\Exception')->during__invoke($message, array(
            'instant_retry_attempts' => 3,
            'instant_retry_delay' => 1000
        ));
    }
}
