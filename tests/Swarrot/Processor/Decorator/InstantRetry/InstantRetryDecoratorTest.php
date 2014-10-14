<?php

namespace Swarrot\Processor\Decorator\InstantRetry;

use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTestCase;
use Swarrot\Broker\Message;

class InstantRetryDecoratorTest extends ProphecyTestCase
{
    public function test_it_is_initializable_without_a_logger()
    {
        $processor = new InstantRetryDecorator();
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $logger = $this->prophesize('Psr\Log\LoggerInterface');

        $processor = new InstantRetryDecorator($logger->reveal());
    }

    public function test_it_should_return_void_when_no_exception_is_thrown()
    {
        $decoratedProcessor = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $logger    = $this->prophesize('Psr\Log\LoggerInterface');

        $message = new Message('body', array(), 1);

        $processor = new InstantRetryDecorator($logger->reveal());

        $this->assertNull(
            $processor->decorate($decoratedProcessor->reveal(), $message, array(
                'instant_retry_attempts' => 3,
                'instant_retry_delay' => 1000
            ))
        );
    }

    public function test_it_should_throw_an_exception_after_consecutive_failed()
    {
        $decoratedProcessor = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $logger    = $this->prophesize('Psr\Log\LoggerInterface');

        $message = new Message('body', array(), 1);

        $decoratedProcessor->process(
            Argument::type('Swarrot\Broker\Message'),
            Argument::exact(array(
                'instant_retry_attempts' => 3,
                'instant_retry_delay' => 1000
            ))
        )
        ->shouldBeCalledTimes(3)
        ->willThrow('\BadMethodCallException');

        $processor = new InstantRetryDecorator($logger->reveal());

        $this->setExpectedException('\BadMethodCallException');
        $processor->decorate($decoratedProcessor->reveal(), $message, array(
            'instant_retry_attempts' => 3,
            'instant_retry_delay' => 1000
        ));
    }
}
