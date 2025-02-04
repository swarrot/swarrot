<?php

namespace Swarrot\Tests\Processor\XDeath;

use PhpAmqpLib\Wire\AMQPArray;
use PhpAmqpLib\Wire\AMQPTable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\XDeath\XDeathMaxLifetimeProcessor;
use Symfony\Component\OptionsResolver\OptionsResolver;

class XDeathMaxLifetimeProcessorTest extends TestCase
{
    use ProphecyTrait;

    public function test_it_is_initializable_without_a_logger()
    {
        $processorMock = $this->prophesize(ProcessorInterface::class);

        $callback = function () {
        };

        $processor = new XDeathMaxLifetimeProcessor($processorMock->reveal(), 'good_queue', $callback);
        $this->assertInstanceOf(XDeathMaxLifetimeProcessor::class, $processor);
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $processorMock = $this->prophesize(ProcessorInterface::class);

        $callback = function () {
        };

        $logger = $this->prophesize(LoggerInterface::class);

        $processor = new XDeathMaxLifetimeProcessor($processorMock->reveal(), 'good_queue', $callback, $logger->reveal());
        $this->assertInstanceOf(XDeathMaxLifetimeProcessor::class, $processor);
    }

    public function test_it_should_return_result_when_all_is_right()
    {
        $message = new Message('body', [], 1);

        $processorMock = $this->prophesize(ProcessorInterface::class);
        $processorMock->process($message, [])->willReturn(true)->shouldBeCalledTimes(1);

        $callback = function (): bool {
            return false;
        };

        $logger = $this->prophesize(LoggerInterface::class);

        $processor = new XDeathMaxLifetimeProcessor($processorMock->reveal(), 'good_queue', $callback, $logger->reveal());
        $this->assertTrue($processor->process($message, []));
    }

    public function test_it_should_rethrow_when_an_exception_occurred()
    {
        $this->expectException('\BadMethodCallException');

        $message = new Message('body', [], 1);

        $options = [];

        $processorMock = $this->prophesize(ProcessorInterface::class);
        $processorMock
            ->process(
                Argument::exact($message),
                Argument::exact($options)
            )->willThrow('\BadMethodCallException')
            ->shouldBeCalledTimes(1);

        $callback = function () {
        };

        $logger = $this->prophesize(LoggerInterface::class);

        $processor = new XDeathMaxLifetimeProcessor($processorMock->reveal(), 'good_queue', $callback, $logger->reveal());
        $this->assertNull($processor->process($message, $options));
    }

    public function test_it_should_return_a_valid_array_of_option()
    {
        $processorMock = $this->prophesize(ProcessorInterface::class);

        $callback = function () {
        };

        $optionsResolver = new OptionsResolver();
        $logger = $this->prophesize(LoggerInterface::class);

        $processor = new XDeathMaxLifetimeProcessor($processorMock->reveal(), 'good_queue', $callback, $logger->reveal());
        $processor->setDefaultOptions($optionsResolver);

        $config = $optionsResolver->resolve([]);

        $this->assertEquals([
            'x_death_max_lifetime' => 3600,
            'x_death_max_lifetime_log_levels_map' => [],
            'x_death_max_lifetime_fail_log_levels_map' => [],
        ], $config);
    }

    public static function messageProvider()
    {
        $data = [
            [
                new Message(
                    null,
                    [
                        'headers' => [
                            'x-death' => [
                                ['count' => 1],
                                ['queue' => 'other_queue', 'count' => 2],
                                ['queue' => 'good_queue', 'time' => time() - 5],
                            ],
                        ],
                    ]
                ),
            ],
        ];

        if (class_exists('\AMQPTimestamp')) {
            $data[] = [
                new Message(
                    null,
                    [
                        'headers' => [
                            'x-death' => [
                                ['time' => new \AMQPTimestamp(time() - 50)],
                                ['queue' => 'other_queue', 'time' => new \AMQPTimestamp(time() - 50)],
                                ['queue' => 'good_queue', 'time' => new \AMQPTimestamp(time() - 50)],
                            ],
                        ],
                    ]
                ),
            ];
        }

        if (class_exists('PhpAmqpLib\Wire\AMQPArray') && class_exists('PhpAmqpLib\Wire\AMQPTable')) {
            $data[] = [
                new Message(
                    null,
                    [
                        'headers' => [
                            'x-death' => new AMQPArray([
                                new AMQPTable([
                                    'time' => new \DateTime('10 seconds'),
                                ]),
                                new AMQPTable([
                                    'queue' => 'other_queue',
                                    'time' => new \DateTime('15 seconds'),
                                ]),
                                new AMQPTable([
                                    'queue' => 'good_queue',
                                    'time' => new \DateTime('-5 seconds'),
                                ]),
                            ]),
                        ],
                    ]
                ),
            ];
        }

        return $data;
    }

    #[DataProvider('messageProvider')]
    public function test_it_should_not_rethrow_with_x_death_max_lifetime_reached(Message $message)
    {
        $options = [
            'x_death_max_lifetime' => 1,
            'x_death_max_lifetime_log_levels_map' => [],
            'x_death_max_lifetime_fail_log_levels_map' => [],
        ];

        $processorMock = $this->prophesize(ProcessorInterface::class);
        $processorMock
            ->process(Argument::exact($message), Argument::exact($options))
            ->willThrow('\BadMethodCallException')
            ->shouldBeCalledTimes(1);

        $callback = function (): bool {
            return true;
        };

        $logger = $this->prophesize(LoggerInterface::class);
        $logger
            ->log(
                'warning',
                Argument::that(function ($value) {
                    return preg_match('/\[XDeathMaxLifetime\] Max lifetime reached. \d+\/1 seconds exceed. Execute the configured callback\./', $value);
                }),
                Argument::cetera()
            )
            ->shouldBeCalled();

        $processor = new XDeathMaxLifetimeProcessor($processorMock->reveal(), 'good_queue', $callback, $logger->reveal());
        $this->assertTrue($processor->process($message, $options));
    }

    #[DataProvider('messageProvider')]
    public function test_it_should_rethrow_with_x_death_max_lifetime_reached_and_callback_return_null(Message $message)
    {
        $this->expectException(\BadMethodCallException::class);

        $options = [
            'x_death_max_lifetime' => 1,
            'x_death_max_lifetime_log_levels_map' => [],
            'x_death_max_lifetime_fail_log_levels_map' => [],
        ];

        $processorMock = $this->prophesize(ProcessorInterface::class);
        $processorMock
            ->process($message, $options)
            ->willThrow(new \BadMethodCallException())
            ->shouldBeCalledTimes(1);

        $callback = function () {
            return null;
        };

        $logger = $this->prophesize(LoggerInterface::class);
        $logger
            ->log(
                'warning',
                Argument::that(function ($value) {
                    return preg_match('/\[XDeathMaxLifetime\] Max lifetime reached. \d+\/1 seconds exceed. Execute the configured callback\./', $value);
                }),
                Argument::cetera()
            )
            ->shouldBeCalled();

        $processor = new XDeathMaxLifetimeProcessor($processorMock->reveal(), 'good_queue', $callback, $logger->reveal());
        $processor->process($message, $options);
    }

    #[DataProvider('messageProvider')]
    public function test_it_should_rethrow_with_x_death_max_lifetime_not_reached(Message $message)
    {
        $this->expectException('\BadMethodCallException');

        $options = [
            'x_death_max_lifetime' => 60,
            'x_death_max_lifetime_log_levels_map' => [],
            'x_death_max_lifetime_fail_log_levels_map' => [],
        ];

        $processorMock = $this->prophesize(ProcessorInterface::class);
        $processorMock
            ->process(Argument::exact($message), Argument::exact($options))
            ->willThrow('\BadMethodCallException')
            ->shouldBeCalledTimes(1);

        $callback = function () {
            return;
        };

        $logger = $this->prophesize(LoggerInterface::class);
        $logger
            ->log(
                'warning',
                Argument::that(function ($value) {
                    return preg_match('/\[XDeathMaxLifetime\] Lifetime remain \d+\/60 seconds\./', $value);
                }),
                Argument::cetera()
            )
            ->shouldBeCalled();

        $processor = new XDeathMaxLifetimeProcessor($processorMock->reveal(), 'good_queue', $callback, $logger->reveal());
        $processor->process($message, $options);
    }

    #[DataProvider('messageProvider')]
    public function test_it_should_log_a_custom_log_level_with_x_death_max_lifetime_reached(Message $message)
    {
        $options = [
            'x_death_max_lifetime' => 1,
            'x_death_max_lifetime_log_levels_map' => [],
            'x_death_max_lifetime_fail_log_levels_map' => [
                '\BadMethodCallException' => LogLevel::CRITICAL,
            ],
        ];

        $processorMock = $this->prophesize(ProcessorInterface::class);
        $processorMock
            ->process(Argument::exact($message), Argument::exact($options))
            ->willThrow('\BadMethodCallException')
            ->shouldBeCalledTimes(1);

        $callback = function (): bool {
            return true;
        };

        $logger = $this->prophesize(LoggerInterface::class);
        $logger
            ->log(
                'critical',
                Argument::that(function ($value) {
                    return preg_match('/\[XDeathMaxLifetime\] Max lifetime reached. \d+\/1 seconds exceed. Execute the configured callback\./', $value);
                }),
                Argument::cetera()
            )
            ->shouldBeCalled();

        $processor = new XDeathMaxLifetimeProcessor($processorMock->reveal(), 'good_queue', $callback, $logger->reveal());
        $this->assertTrue($processor->process($message, $options));
    }

    #[DataProvider('messageProvider')]
    public function test_it_should_log_a_custom_log_level_with_x_death_max_lifetime_not_reached(Message $message)
    {
        $this->expectException('\BadMethodCallException');

        $options = [
            'x_death_max_lifetime' => 60,
            'x_death_max_lifetime_log_levels_map' => [
                '\BadMethodCallException' => LogLevel::CRITICAL,
            ],
            'x_death_max_lifetime_fail_log_levels_map' => [],
        ];

        $processorMock = $this->prophesize(ProcessorInterface::class);
        $processorMock
            ->process(Argument::exact($message), Argument::exact($options))
            ->willThrow('\BadMethodCallException')
            ->shouldBeCalledTimes(1);

        $callback = function () {
            return;
        };

        $logger = $this->prophesize(LoggerInterface::class);
        $logger
            ->log(
                'critical',
                Argument::that(function ($value) {
                    return preg_match('/\[XDeathMaxLifetime\] Lifetime remain \d+\/60 seconds./', $value);
                }),
                Argument::cetera()
            )
            ->shouldBeCalled();

        $processor = new XDeathMaxLifetimeProcessor($processorMock->reveal(), 'good_queue', $callback, $logger->reveal());
        $processor->process($message, $options);
    }

    #[DataProvider('messageProvider')]
    public function test_it_should_log_x_death_max_lifetime_not_found(Message $message)
    {
        $this->expectException('\BadMethodCallException');

        $options = [
            'x_death_max_lifetime' => 10,
            'x_death_max_lifetime_log_levels_map' => [],
            'x_death_max_lifetime_fail_log_levels_map' => [],
        ];

        $processorMock = $this->prophesize(ProcessorInterface::class);
        $processorMock
            ->process(Argument::exact($message), Argument::exact($options))
            ->willThrow('\BadMethodCallException')
            ->shouldBeCalledTimes(1);

        $callback = function () {
            return;
        };

        $logger = $this->prophesize(LoggerInterface::class);
        $logger
            ->log('warning', '[XDeathMaxLifetime] No x-death header found for queue name "not_found_queue". Do nothing.', Argument::cetera())
            ->shouldBeCalled();

        $processor = new XDeathMaxLifetimeProcessor($processorMock->reveal(), 'not_found_queue', $callback, $logger->reveal());
        $processor->process($message, $options);
    }
}
