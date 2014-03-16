<?php

namespace spec\Swarrot\Processor\InstantRetry;

use Prophecy\Argument;
use Swarrot\Processor\InstantRetry\InstantRetryProcessor;
use Swarrot\Broker\Message;

class InstantRetryProcessorTest extends \PHPUnit_Framework_TestCase
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
        $processor = $this->prophet->prophesize('Swarrot\Processor\ProcessorInterface');

        $processor = new InstantRetryProcessor($processor->reveal());
        $this->assertInstanceOf('Swarrot\Processor\InstantRetry\InstantRetryProcessor', $processor);
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $processor = $this->prophet->prophesize('Swarrot\Processor\ProcessorInterface');
        $logger    = $this->prophet->prophesize('Psr\Log\LoggerInterface');

        $processor = new InstantRetryProcessor($processor->reveal(), $logger->reveal());
        $this->assertInstanceOf('Swarrot\Processor\InstantRetry\InstantRetryProcessor', $processor);
    }

    public function test_it_should_return_void_when_no_exception_is_thrown()
    {
        $processor = $this->prophet->prophesize('Swarrot\Processor\ProcessorInterface');
        $logger    = $this->prophet->prophesize('Psr\Log\LoggerInterface');

        $message = new Message(1, 'body');

        $processor = new InstantRetryProcessor($processor->reveal(), $logger->reveal());

        $this->assertNull(
            $processor->process($message, array(
                'instant_retry_attempts' => 3,
                'instant_retry_delay' => 1000
            ))
        );
    }

    public function test_it_should_throw_an_exception_after_consecutive_failed()
    {
        $processor = $this->prophet->prophesize('Swarrot\Processor\ProcessorInterface');
        $logger    = $this->prophet->prophesize('Psr\Log\LoggerInterface');

        $message = new Message(1, 'body');

        $processor->process(
            Argument::type('Swarrot\Broker\Message'),
            Argument::exact(array(
                'instant_retry_attempts' => 3,
                'instant_retry_delay' => 1000
            ))
        )
        ->shouldBeCalledTimes(3)
        ->willThrow('\BadMethodCallException');

        $processor = new InstantRetryProcessor($processor->reveal(), $logger->reveal());

        $this->setExpectedException('\BadMethodCallException');
        $processor->process($message, array(
            'instant_retry_attempts' => 3,
            'instant_retry_delay' => 1000
        ));
    }
}
