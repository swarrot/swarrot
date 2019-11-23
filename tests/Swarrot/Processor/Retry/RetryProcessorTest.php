<?php

namespace Swarrot\Tests\Processor\Retry;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Swarrot\Broker\Message;
use Swarrot\Broker\MessagePublisher\MessagePublisherInterface;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\Retry\RetryProcessor;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RetryProcessorTest extends TestCase
{
    public function test_it_is_initializable_without_a_logger()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $messagePublisher = $this->prophesize(MessagePublisherInterface::class);

        $processor = new RetryProcessor($processor->reveal(), $messagePublisher->reveal());
        $this->assertInstanceOf(RetryProcessor::class, $processor);
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $messagePublisher = $this->prophesize(MessagePublisherInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $processor = new RetryProcessor($processor->reveal(), $messagePublisher->reveal(), $logger->reveal());
        $this->assertInstanceOf(RetryProcessor::class, $processor);
    }

    public function test_it_should_return_result_when_all_is_right()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $messagePublisher = $this->prophesize(MessagePublisherInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $message = new Message('body', [], 1);

        $processor->process(Argument::exact($message), Argument::exact([]))->willReturn(null);
        $messagePublisher
            ->publish(Argument::exact($message))
            ->shouldNotBeCalled(null)
        ;

        $processor = new RetryProcessor($processor->reveal(), $messagePublisher->reveal(), $logger->reveal());
        $this->assertNull($processor->process($message, []));
    }

    public function test_it_should_republished_message_when_an_exception_occurred()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $messagePublisher = $this->prophesize(MessagePublisherInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $message = new Message('body', [], 1);

        $retryProcessor = new RetryProcessor($processor->reveal(), $messagePublisher->reveal(), $logger->reveal());

        $optionsResolver = new OptionsResolver();
        $retryProcessor->setDefaultOptions($optionsResolver);

        $options = $optionsResolver->resolve([
            'retry_attempts' => 3,
            'retry_key_pattern' => 'key_%attempt%',
            'retry_log_levels_map' => [],
            'retry_fail_log_levels_map' => [],
        ]);

        $processor
            ->process(
                Argument::exact($message),
                Argument::exact($options)
            )->willThrow('\BadMethodCallException')
            ->shouldBeCalledTimes(1)
        ;
        $messagePublisher
            ->publish(
                Argument::type(Message::class),
                Argument::exact('key_1')
            )
            ->willReturn(null)
            ->shouldBeCalledTimes(1)
        ;

        $this->assertNull(
            $retryProcessor->process($message, $options)
        );
    }

    public function test_it_should_republished_message_with_incremented_attempts()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $messagePublisher = $this->prophesize(MessagePublisherInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $message = new Message('body', ['headers' => ['swarrot_retry_attempts' => 1]], 1);

        $retryProcessor = new RetryProcessor($processor->reveal(), $messagePublisher->reveal(), $logger->reveal());

        $optionsResolver = new OptionsResolver();
        $retryProcessor->setDefaultOptions($optionsResolver);

        $options = $optionsResolver->resolve([
            'retry_attempts' => 3,
            'retry_key_pattern' => 'key_%attempt%',
            'retry_log_levels_map' => [],
            'retry_fail_log_levels_map' => [],
        ]);

        $processor
            ->process(
                Argument::exact($message),
                Argument::exact($options)
            )->willThrow('\BadMethodCallException')
            ->shouldBeCalledTimes(1)
        ;
        $messagePublisher
            ->publish(
                Argument::that(function (Message $message) {
                    $properties = $message->getProperties();

                    return 2 === $properties['headers']['swarrot_retry_attempts'] && 'body' === $message->getBody();
                }),

                Argument::exact('key_2')
            )
            ->willReturn(null)
            ->shouldBeCalledTimes(1)
        ;

        $this->assertNull(
            $retryProcessor->process($message, $options)
        );
    }

    public function test_it_should_republish_message_with_custom_key()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $messagePublisher = $this->prophesize(MessagePublisherInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $message = new Message('body', ['headers' => ['swarrot_retry_attempts' => 1]], 1);

        $retryProcessor = new RetryProcessor($processor->reveal(), $messagePublisher->reveal(), $logger->reveal());

        $optionsResolver = new OptionsResolver();
        $retryProcessor->setDefaultOptions($optionsResolver);

        $options = $optionsResolver->resolve([
            'retry_attempts' => 3,
            'retry_key_generator' => function ($attempts, Message $message) {
                // Using the id here is not actually useful for a working setup. An actual implementation would probably rather inspect some properties.
                return 'test_'.$attempts.'_'.$message->getId();
            },
            'retry_log_levels_map' => [],
            'retry_fail_log_levels_map' => [],
        ]);

        $processor
            ->process(
                Argument::exact($message),
                Argument::exact($options)
            )->willThrow('\BadMethodCallException')
            ->shouldBeCalledTimes(1)
        ;
        $messagePublisher
            ->publish(
                Argument::that(function (Message $message) {
                    $properties = $message->getProperties();

                    return 2 === $properties['headers']['swarrot_retry_attempts'] && 'body' === $message->getBody();
                }),

                Argument::exact('test_2_1')
            )
            ->willReturn(null)
            ->shouldBeCalledTimes(1)
        ;

        $this->assertNull(
            $retryProcessor->process($message, $options)
        );
    }

    public function test_it_should_throw_exception_if_max_attempts_is_reached()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $messagePublisher = $this->prophesize(MessagePublisherInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $message = new Message('body', ['headers' => ['swarrot_retry_attempts' => 3]], 1);

        $retryProcessor = new RetryProcessor($processor->reveal(), $messagePublisher->reveal(), $logger->reveal());

        $optionsResolver = new OptionsResolver();
        $retryProcessor->setDefaultOptions($optionsResolver);

        $options = $optionsResolver->resolve([
            'retry_attempts' => 3,
            'retry_key_pattern' => 'key_%attempt%',
            'retry_log_levels_map' => [],
            'retry_fail_log_levels_map' => [],
        ]);

        $processor
            ->process(
                Argument::exact($message),
                Argument::exact($options)
            )->willThrow('\BadMethodCallException')
            ->shouldBeCalledTimes(1)
        ;
        $messagePublisher
            ->publish(
                Argument::type(Message::class),
                Argument::exact('key_1')
            )
            ->shouldNotBeCalled()
        ;

        $this->expectException('\BadMethodCallException');

        $retryProcessor->process($message, $options);
    }

    public function test_it_should_return_a_valid_array_of_option()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $messagePublisher = $this->prophesize(MessagePublisherInterface::class);

        $processor = new RetryProcessor($processor->reveal(), $messagePublisher->reveal());

        $optionsResolver = new OptionsResolver();
        $processor->setDefaultOptions($optionsResolver);

        $config = $optionsResolver->resolve([
            'retry_key_pattern' => 'key_%attempt%',
        ]);

        // retry_key_generator is tested separately, because closures cannot be reproduced for assertEquals
        $this->assertArrayHasKey('retry_key_generator', $config);
        $this->assertInstanceOf(\Closure::class, $config['retry_key_generator']);
        unset($config['retry_key_generator']);

        $this->assertEquals([
            'retry_key_pattern' => 'key_%attempt%',
            'retry_attempts' => 3,
            'retry_log_levels_map' => [],
            'retry_fail_log_levels_map' => [],
        ], $config);
    }

    public function test_it_should_require_configuring_the_retry_key()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $messagePublisher = $this->prophesize(MessagePublisherInterface::class);

        $retryProcessor = new RetryProcessor($processor->reveal(), $messagePublisher->reveal());

        $optionsResolver = new OptionsResolver();
        $retryProcessor->setDefaultOptions($optionsResolver);

        $this->expectException(MissingOptionsException::class);

        $optionsResolver->resolve([]);
    }

    public function test_it_should_keep_original_message_properties()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $messagePublisher = $this->prophesize(MessagePublisherInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $message = new Message('body', ['delivery_mode' => 2, 'app_id' => 'applicationId', 'headers' => ['swarrot_retry_attempts' => 1]], 1);

        $retryProcessor = new RetryProcessor($processor->reveal(), $messagePublisher->reveal(), $logger->reveal());

        $optionsResolver = new OptionsResolver();
        $retryProcessor->setDefaultOptions($optionsResolver);

        $options = $optionsResolver->resolve([
            'retry_attempts' => 3,
            'retry_key_pattern' => 'key_%attempt%',
            'retry_log_levels_map' => [],
            'retry_fail_log_levels_map' => [],
        ]);

        $processor
            ->process(
                Argument::exact($message),
                Argument::exact($options)
            )->willThrow('\BadMethodCallException')
            ->shouldBeCalledTimes(1)
        ;

        $messagePublisher
            ->publish(
                Argument::that(function (Message $message) {
                    $properties = $message->getProperties();

                    return 2 === $properties['delivery_mode'] && 'applicationId' === $properties['app_id'];
                }),

                Argument::exact('key_2')
            )
            ->willReturn(null)
            ->shouldBeCalledTimes(1)
        ;

        $this->assertNull(
            $retryProcessor->process($message, $options)
        );
    }

    public function test_it_should_keep_original_message_headers()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $messagePublisher = $this->prophesize(MessagePublisherInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $message = new Message('body', ['headers' => [
            'string' => 'foo',
            'integer' => 42,
        ]], 1);

        $retryProcessor = new RetryProcessor($processor->reveal(), $messagePublisher->reveal(), $logger->reveal());

        $optionsResolver = new OptionsResolver();
        $retryProcessor->setDefaultOptions($optionsResolver);

        $options = $optionsResolver->resolve([
            'retry_attempts' => 3,
            'retry_key_pattern' => 'key_%attempt%',
            'retry_log_levels_map' => [],
            'retry_fail_log_levels_map' => [],
        ]);

        $processor
            ->process(
                Argument::exact($message),
                Argument::exact($options)
            )->willThrow('\BadMethodCallException')
            ->shouldBeCalledTimes(1)
        ;

        $messagePublisher
            ->publish(
                Argument::that(function (Message $message) {
                    $properties = $message->getProperties();

                    return 1 === $properties['headers']['swarrot_retry_attempts'] && 'foo' === $properties['headers']['string'] && 42 === $properties['headers']['integer'];
                }),
                Argument::exact('key_1')
            )
            ->willReturn(null)
            ->shouldBeCalledTimes(1)
        ;

        $this->assertNull(
            $retryProcessor->process($message, $options)
        );
    }

    public function test_it_should_log_a_warning_by_default_when_an_exception_occurred()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $messagePublisher = $this->prophesize(MessagePublisherInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);
        $exception = new \BadMethodCallException();

        $message = new Message('body', [], 1);

        $retryProcessor = new RetryProcessor($processor->reveal(), $messagePublisher->reveal(), $logger->reveal());

        $optionsResolver = new OptionsResolver();
        $retryProcessor->setDefaultOptions($optionsResolver);

        $options = $optionsResolver->resolve([
            'retry_attempts' => 3,
            'retry_key_pattern' => 'key_%attempt%',
            'retry_log_levels_map' => [],
            'retry_fail_log_levels_map' => [],
        ]);

        $processor
            ->process(
                Argument::exact($message),
                Argument::exact($options)
            )->willThrow($exception)
            ->shouldBeCalledTimes(1)
        ;

        $logger
            ->log(
                Argument::exact(LogLevel::WARNING),
                Argument::exact('[Retry] An exception occurred. Republishing message.'),
                Argument::exact([
                    'swarrot_processor' => 'retry',
                    'exception' => $exception,
                    'number_of_attempts' => 1,
                    'key' => 'key_1',
                ])
            )
            ->shouldBeCalledTimes(1)
        ;

        $this->assertNull(
            $retryProcessor->process($message, $options)
        );
    }

    public function test_it_should_log_a_custom_log_level_when_an_exception_occurred()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $messagePublisher = $this->prophesize(MessagePublisherInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);
        $exception = new \BadMethodCallException();

        $message = new Message('body', [], 1);

        $retryProcessor = new RetryProcessor($processor->reveal(), $messagePublisher->reveal(), $logger->reveal());

        $optionsResolver = new OptionsResolver();
        $retryProcessor->setDefaultOptions($optionsResolver);

        $options = $optionsResolver->resolve([
            'retry_attempts' => 3,
            'retry_key_pattern' => 'key_%attempt%',
            'retry_log_levels_map' => [
                '\BadMethodCallException' => LogLevel::CRITICAL,
            ],
            'retry_fail_log_levels_map' => [],
        ]);

        $processor
            ->process(
                Argument::exact($message),
                Argument::exact($options)
            )->willThrow($exception)
            ->shouldBeCalledTimes(1)
        ;

        $logger
            ->log(
                Argument::exact(LogLevel::CRITICAL),
                Argument::exact('[Retry] An exception occurred. Republishing message.'),
                Argument::exact([
                    'swarrot_processor' => 'retry',
                    'exception' => $exception,
                    'number_of_attempts' => 1,
                    'key' => 'key_1',
                ])
            )
            ->shouldBeCalledTimes(1)
        ;

        $this->assertNull(
            $retryProcessor->process($message, $options)
        );
    }

    public function test_it_should_log_a_custom_log_level_when_a_child_exception_occurred()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $messagePublisher = $this->prophesize(MessagePublisherInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);
        $exception = new \BadMethodCallException();

        $message = new Message('body', [], 1);

        $retryProcessor = new RetryProcessor($processor->reveal(), $messagePublisher->reveal(), $logger->reveal());

        $optionsResolver = new OptionsResolver();
        $retryProcessor->setDefaultOptions($optionsResolver);

        $options = $optionsResolver->resolve([
            'retry_attempts' => 3,
            'retry_key_pattern' => 'key_%attempt%',
            'retry_log_levels_map' => [
                '\LogicException' => LogLevel::CRITICAL,
            ],
            'retry_fail_log_levels_map' => [],
        ]);

        $processor
            ->process(
                Argument::exact($message),
                Argument::exact($options)
            )->willThrow($exception)
            ->shouldBeCalledTimes(1)
        ;

        $logger
            ->log(
                Argument::exact(LogLevel::CRITICAL),
                Argument::exact('[Retry] An exception occurred. Republishing message.'),
                Argument::exact([
                    'swarrot_processor' => 'retry',
                    'exception' => $exception,
                    'number_of_attempts' => 1,
                    'key' => 'key_1',
                ])
            )
            ->shouldBeCalledTimes(1)
        ;

        $this->assertNull(
            $retryProcessor->process($message, $options)
        );
    }

    public function test_it_should_log_a_warning_by_default_if_max_attempts_is_reached()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $messagePublisher = $this->prophesize(MessagePublisherInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);
        $exception = new \BadMethodCallException();

        $message = new Message('body', ['headers' => ['swarrot_retry_attempts' => 3]], 1);

        $retryProcessor = new RetryProcessor($processor->reveal(), $messagePublisher->reveal(), $logger->reveal());

        $optionsResolver = new OptionsResolver();
        $retryProcessor->setDefaultOptions($optionsResolver);

        $options = $optionsResolver->resolve([
            'retry_attempts' => 3,
            'retry_key_pattern' => 'key_%attempt%',
            'retry_log_levels_map' => [],
            'retry_fail_log_levels_map' => [],
        ]);

        $processor
            ->process(
                Argument::exact($message),
                Argument::exact($options)
            )->willThrow($exception)
            ->shouldBeCalledTimes(1)
        ;

        $logger
            ->log(
                Argument::exact(LogLevel::WARNING),
                Argument::exact('[Retry] Stop attempting to process message.'),
                Argument::exact([
                    'swarrot_processor' => 'retry',
                    'exception' => $exception,
                    'number_of_attempts' => 4,
                ])
            )
            ->shouldBeCalledTimes(1)
        ;

        $this->expectException('\BadMethodCallException');

        $retryProcessor->process($message, $options);
    }

    public function test_it_should_log_a_custom_log_level_if_max_attempts_is_reached()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $messagePublisher = $this->prophesize(MessagePublisherInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);
        $exception = new \BadMethodCallException();

        $message = new Message('body', ['headers' => ['swarrot_retry_attempts' => 3]], 1);

        $retryProcessor = new RetryProcessor($processor->reveal(), $messagePublisher->reveal(), $logger->reveal());

        $optionsResolver = new OptionsResolver();
        $retryProcessor->setDefaultOptions($optionsResolver);

        $options = $optionsResolver->resolve([
            'retry_attempts' => 3,
            'retry_key_pattern' => 'key_%attempt%',
            'retry_log_levels_map' => [],
            'retry_fail_log_levels_map' => [
                '\BadMethodCallException' => LogLevel::CRITICAL,
            ],
        ]);

        $processor
            ->process(
                Argument::exact($message),
                Argument::exact($options)
            )->willThrow($exception)
            ->shouldBeCalledTimes(1)
        ;

        $logger
            ->log(
                Argument::exact(LogLevel::CRITICAL),
                Argument::exact('[Retry] Stop attempting to process message.'),
                Argument::exact([
                    'swarrot_processor' => 'retry',
                    'exception' => $exception,
                    'number_of_attempts' => 4,
                ])
            )
            ->shouldBeCalledTimes(1)
        ;

        $this->expectException('\BadMethodCallException');

        $retryProcessor->process($message, $options);
    }

    public function test_it_should_log_a_custom_log_level_if_max_attempts_is_reached_for_child_exception()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $messagePublisher = $this->prophesize(MessagePublisherInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);
        $exception = new \BadMethodCallException();

        $message = new Message('body', ['headers' => ['swarrot_retry_attempts' => 3]], 1);

        $retryProcessor = new RetryProcessor($processor->reveal(), $messagePublisher->reveal(), $logger->reveal());

        $optionsResolver = new OptionsResolver();
        $retryProcessor->setDefaultOptions($optionsResolver);

        $options = $optionsResolver->resolve([
            'retry_attempts' => 3,
            'retry_key_pattern' => 'key_%attempt%',
            'retry_log_levels_map' => [],
            'retry_fail_log_levels_map' => [
                '\LogicException' => LogLevel::CRITICAL,
            ],
        ]);

        $processor
            ->process(
                Argument::exact($message),
                Argument::exact($options)
            )->willThrow($exception)
            ->shouldBeCalledTimes(1)
        ;

        $logger
            ->log(
                Argument::exact(LogLevel::CRITICAL),
                Argument::exact('[Retry] Stop attempting to process message.'),
                Argument::exact([
                    'swarrot_processor' => 'retry',
                    'exception' => $exception,
                    'number_of_attempts' => 4,
                ])
            )
            ->shouldBeCalledTimes(1)
        ;

        $this->expectException('\BadMethodCallException');

        $retryProcessor->process($message, $options);
    }
}
