<?php

namespace Swarrot\Tests\Processor\InstantRetry;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LogLevel;
use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;
use Psr\Log\LoggerInterface;
use Swarrot\Processor\InstantRetry\InstantRetryProcessor;

class InstantRetryProcessorTest extends TestCase
{
    public function test_it_is_initializable_without_a_logger()
    {
        $processor = $this->prophesize(ProcessorInterface::class);

        $processor = new InstantRetryProcessor($processor->reveal());
        $this->assertInstanceOf(InstantRetryProcessor::class, $processor);
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $processor = new InstantRetryProcessor($processor->reveal(), $logger->reveal());
        $this->assertInstanceOf(InstantRetryProcessor::class, $processor);
    }

    public function test_it_should_return_void_when_no_exception_is_thrown()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);

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
        $processor = $this->prophesize(ProcessorInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $message = new Message('body', array(), 1);

        $processor->process(
            Argument::type(Message::class),
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
        $processor = $this->prophesize(ProcessorInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);
        $exception = new \BadMethodCallException();

        $message = new Message('body', array(), 1);

        $processor->process(
            Argument::type(Message::class),
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
                Argument::exact('[InstantRetry] An exception occurred. The message will be processed again.'),
                Argument::exact(array(
                    'swarrot_processor' => 'instant_retry',
                    'exception' => $exception,
                    'message_id' => 1,
                    'instant_retry_delay' => 1,
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
        $processor = $this->prophesize(ProcessorInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);
        $exception = new \BadMethodCallException();

        $message = new Message('body', array(), 1);

        $processor->process(
            Argument::type(Message::class),
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
                Argument::exact('[InstantRetry] An exception occurred. The message will be processed again.'),
                Argument::exact(array(
                    'swarrot_processor' => 'instant_retry',
                    'exception' => $exception,
                    'message_id' => 1,
                    'instant_retry_delay' => 1,
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
        $processor = $this->prophesize(ProcessorInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);
        $exception = new \BadMethodCallException();

        $message = new Message('body', array(), 1);

        $processor->process(
            Argument::type(Message::class),
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
                Argument::exact('[InstantRetry] An exception occurred. The message will be processed again.'),
                Argument::exact(array(
                    'swarrot_processor' => 'instant_retry',
                    'exception' => $exception,
                    'message_id' => 1,
                    'instant_retry_delay' => 1,
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
