<?php

namespace spec\Swarrot\Processor\InstantRetry;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LogLevel;
use Swarrot\Processor\InstantRetry\InstantRetryProcessor;
use Swarrot\Broker\Message;

class InstantRetryProcessorTest extends TestCase
{
    public function test_it_is_initializable_without_a_logger()
    {
        $processor = $this->prophesize('Swarrot\Processor\ProcessorInterface');

        $processor = new InstantRetryProcessor($processor->reveal());
        $this->assertInstanceOf('Swarrot\Processor\InstantRetry\InstantRetryProcessor', $processor);
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $processor = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $logger    = $this->prophesize('Psr\Log\LoggerInterface');

        $processor = new InstantRetryProcessor($processor->reveal(), $logger->reveal());
        $this->assertInstanceOf('Swarrot\Processor\InstantRetry\InstantRetryProcessor', $processor);
    }

    public function test_it_should_return_void_when_no_exception_is_thrown()
    {
        $processor = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $logger    = $this->prophesize('Psr\Log\LoggerInterface');

        $message = new Message('body', array(), 1);

        $processor = new InstantRetryProcessor($processor->reveal(), $logger->reveal());

        $this->assertNull(
            $processor->process($message, array(
                'instant_retry_attempts' => 3,
                'instant_retry_delay' => 1000,
                'instant_retry_log_levels_map' => array(),
            ))
        );
    }

    public function test_it_should_throw_an_exception_after_consecutive_failed()
    {
        $processor = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $logger    = $this->prophesize('Psr\Log\LoggerInterface');

        $message = new Message('body', array(), 1);

        $processor->process(
            Argument::type('Swarrot\Broker\Message'),
            Argument::exact(array(
                'instant_retry_attempts' => 3,
                'instant_retry_delay' => 1000,
                'instant_retry_log_levels_map' => array(),
            ))
        )
        ->shouldBeCalledTimes(3)
        ->willThrow('\BadMethodCallException');

        $processor = new InstantRetryProcessor($processor->reveal(), $logger->reveal());

        $this->expectException('\BadMethodCallException');
        $processor->process($message, array(
            'instant_retry_attempts' => 3,
            'instant_retry_delay' => 1000,
            'instant_retry_log_levels_map' => array(),
        ));
    }

    public function test_it_should_log_a_warning_by_default_when_an_exception_occurred()
    {
        $processor        = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $logger           = $this->prophesize('Psr\Log\LoggerInterface');
        $exception        = new \BadMethodCallException();

        $message = new Message('body', array(), 1);

        $processor->process(
            Argument::type('Swarrot\Broker\Message'),
            Argument::exact(array(
                'instant_retry_attempts' => 3,
                'instant_retry_delay' => 1000,
                'instant_retry_log_levels_map' => array(),
            ))
        )
        ->shouldBeCalledTimes(3)
        ->willThrow($exception);

        $logger
            ->log(
                Argument::exact(LogLevel::WARNING),
                Argument::exact('[InstantRetry] An exception occurred. Message #1 will be processed again in 1 ms'),
                Argument::exact(array(
                    'swarrot_processor' => 'instant_retry',
                    'exception' => $exception,
                ))
            )
            ->shouldBeCalledTimes(3)
        ;

        $processor = new InstantRetryProcessor($processor->reveal(), $logger->reveal());

        $this->expectException('\BadMethodCallException');
        $processor->process($message, array(
            'instant_retry_attempts' => 3,
            'instant_retry_delay' => 1000,
            'instant_retry_log_levels_map' => array(),
        ));
    }

    public function test_it_should_log_a_custom_log_level_when_an_exception_occurred()
    {
        $processor        = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $logger           = $this->prophesize('Psr\Log\LoggerInterface');
        $exception        = new \BadMethodCallException();

        $message = new Message('body', array(), 1);

        $processor->process(
            Argument::type('Swarrot\Broker\Message'),
            Argument::exact(array(
                'instant_retry_attempts' => 3,
                'instant_retry_delay' => 1000,
                'instant_retry_log_levels_map' => array(
                    '\BadMethodCallException' => LogLevel::CRITICAL,
                ),
            ))
        )
        ->shouldBeCalledTimes(3)
        ->willThrow($exception);

        $logger
            ->log(
                Argument::exact(LogLevel::CRITICAL),
                Argument::exact('[InstantRetry] An exception occurred. Message #1 will be processed again in 1 ms'),
                Argument::exact(array(
                    'swarrot_processor' => 'instant_retry',
                    'exception' => $exception,
                ))
            )
            ->shouldBeCalledTimes(3)
        ;

        $processor = new InstantRetryProcessor($processor->reveal(), $logger->reveal());

        $this->expectException('\BadMethodCallException');
        $processor->process($message, array(
            'instant_retry_attempts' => 3,
            'instant_retry_delay' => 1000,
            'instant_retry_log_levels_map' => array(
                '\BadMethodCallException' => LogLevel::CRITICAL,
            ),
        ));
    }

    public function test_it_should_log_a_custom_log_level_when_a_child_exception_occurred()
    {
        $processor        = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $logger           = $this->prophesize('Psr\Log\LoggerInterface');
        $exception        = new \BadMethodCallException();

        $message = new Message('body', array(), 1);

        $processor->process(
            Argument::type('Swarrot\Broker\Message'),
            Argument::exact(array(
                'instant_retry_attempts' => 3,
                'instant_retry_delay' => 1000,
                'instant_retry_log_levels_map' => array(
                    '\LogicException' => LogLevel::CRITICAL,
                ),
            ))
        )
            ->shouldBeCalledTimes(3)
            ->willThrow($exception);

        $logger
            ->log(
                Argument::exact(LogLevel::CRITICAL),
                Argument::exact('[InstantRetry] An exception occurred. Message #1 will be processed again in 1 ms'),
                Argument::exact(array(
                    'swarrot_processor' => 'instant_retry',
                    'exception' => $exception,
                ))
            )
            ->shouldBeCalledTimes(3)
        ;

        $processor = new InstantRetryProcessor($processor->reveal(), $logger->reveal());

        $this->expectException('\BadMethodCallException');
        $processor->process($message, array(
            'instant_retry_attempts' => 3,
            'instant_retry_delay' => 1000,
            'instant_retry_log_levels_map' => array(
                '\LogicException' => LogLevel::CRITICAL,
            ),
        ));
    }
}
