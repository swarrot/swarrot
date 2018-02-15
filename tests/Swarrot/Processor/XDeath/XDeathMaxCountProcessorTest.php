<?php

namespace Swarrot\Tests\Processor\XDeath;

use PhpAmqpLib\Wire\AMQPArray;
use PhpAmqpLib\Wire\AMQPTable;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\XDeath\XDeathMaxCountProcessor;
use Symfony\Component\OptionsResolver\OptionsResolver;

class XDeathMaxCountProcessorTest extends TestCase
{
    public function test_it_is_initializable_without_a_logger()
    {
        $processorMock = $this->prophesize(ProcessorInterface::class);
        $callback = function () {
        };

        $processor = new XDeathMaxCountProcessor($processorMock->reveal(), 'good_queue', $callback);
        $this->assertInstanceOf(XDeathMaxCountProcessor::class, $processor);
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $processorMock = $this->prophesize(ProcessorInterface::class);

        $callback = function () {
        };

        $logger = $this->prophesize(LoggerInterface::class);

        $processor = new XDeathMaxCountProcessor($processorMock->reveal(), 'good_queue', $callback, $logger->reveal());
        $this->assertInstanceOf(XDeathMaxCountProcessor::class, $processor);
    }

    public function test_it_should_return_result_when_all_is_right()
    {
        $message = new Message('body', array(), 1);

        $processorMock = $this->prophesize(ProcessorInterface::class);
        $processorMock->process(Argument::exact($message), Argument::exact(array()))->willReturn(null);

        $callback = function () {
        };

        $logger = $this->prophesize(LoggerInterface::class);

        $processor = new XDeathMaxCountProcessor($processorMock->reveal(), 'good_queue', $callback, $logger->reveal());
        $this->assertNull($processor->process($message, array()));
    }

    public function test_it_should_rethrow_when_an_exception_occurred()
    {
        $this->expectException('\BadMethodCallException');

        $message = new Message('body', array(), 1);

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

        $processor = new XDeathMaxCountProcessor($processorMock->reveal(), 'good_queue', $callback, $logger->reveal());
        $processor->process($message, $options);
    }

    public function test_it_should_return_a_valid_array_of_option()
    {
        $processorMock = $this->prophesize(ProcessorInterface::class);

        $callback = function () {
        };

        $optionsResolver = new OptionsResolver();
        $logger = $this->prophesize(LoggerInterface::class);

        $processor = new XDeathMaxCountProcessor($processorMock->reveal(), 'good_queue', $callback, $logger->reveal());
        $processor->setDefaultOptions($optionsResolver);

        $config = $optionsResolver->resolve(array());

        $this->assertEquals(array(
            'x_death_max_count' => 300,
            'x_death_max_count_log_levels_map' => array(),
            'x_death_max_count_fail_log_levels_map' => array(),
        ), $config);
    }

    public function messageProvider()
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
                                ['queue' => 'good_queue', 'count' => 4],
                            ],
                        ],
                    ]
                ),
            ],
        ];
        if (class_exists('PhpAmqpLib\Wire\AMQPArray') && class_exists('PhpAmqpLib\Wire\AMQPTable')) {
            $data[] = [
                new Message(
                    null,
                    [
                        'headers' => [
                            'x-death' => new AMQPArray([
                                new AMQPTable([
                                    'count' => '1',
                                ]),
                                new AMQPTable([
                                    'queue' => 'other_queue',
                                    'count' => '2',
                                ]),
                                new AMQPTable([
                                    'queue' => 'good_queue',
                                    'count' => '4',
                                ]),
                            ]),
                        ],
                    ]
                ),
            ];
        }

        return $data;
    }

    /**
     * @dataProvider messageProvider
     *
     * @param $message
     */
    public function test_it_should_not_rethrow_with_x_death_max_count_reached($message)
    {
        $options = array(
            'x_death_max_count' => 1,
            'x_death_max_count_log_levels_map' => array(),
            'x_death_max_count_fail_log_levels_map' => array(),
        );

        $processorMock = $this->prophesize(ProcessorInterface::class);
        $processorMock
            ->process(Argument::exact($message), Argument::exact($options))
            ->willThrow('\BadMethodCallException')
            ->shouldBeCalled();

        $callback = function () {
            return 'my_fake_return';
        };

        $logger = $this->prophesize(LoggerInterface::class);
        $logger
            ->log('warning', '[XDeathMaxCount] Max count reached. 4/1 attempts. Execute the configured callback.', Argument::cetera())
            ->shouldBeCalled();

        $processor = new XDeathMaxCountProcessor($processorMock->reveal(), 'good_queue', $callback, $logger->reveal());
        $this->assertEquals('my_fake_return', $processor->process($message, $options));
    }

    /**
     * @dataProvider messageProvider
     *
     * @param $message
     */
    public function test_it_should_rethrow_with_x_death_max_count_reached($message)
    {
        $this->expectException('\BadMethodCallException');

        $options = array(
            'x_death_max_count' => 1,
            'x_death_max_count_log_levels_map' => array(),
            'x_death_max_count_fail_log_levels_map' => array(),
        );

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
            ->log('warning', '[XDeathMaxCount] Max count reached. 4/1 attempts. Execute the configured callback.', Argument::cetera())
            ->shouldBeCalled();

        $processor = new XDeathMaxCountProcessor($processorMock->reveal(), 'good_queue', $callback, $logger->reveal());
        $processor->process($message, $options);
    }

    /**
     * @dataProvider messageProvider
     *
     * @param $message
     */
    public function test_it_should_rethrow_with_x_death_max_count_not_reached($message)
    {
        $this->expectException('\BadMethodCallException');

        $options = array(
            'x_death_max_count' => 10,
            'x_death_max_count_log_levels_map' => array(),
            'x_death_max_count_fail_log_levels_map' => array(),
        );

        $processorMock = $this->prophesize(ProcessorInterface::class);
        $processorMock
            ->process(Argument::exact($message), Argument::exact($options))
            ->willThrow('\BadMethodCallException')
            ->shouldBeCalledTimes(1);

        $callback = function () {
            return 'my_fake_return';
        };

        $logger = $this->prophesize(LoggerInterface::class);
        $logger
            ->log('warning', '[XDeathMaxCount] 4/10 attempts.', Argument::cetera())
            ->shouldBeCalled();

        $processor = new XDeathMaxCountProcessor($processorMock->reveal(), 'good_queue', $callback, $logger->reveal());
        $processor->process($message, $options);
    }

    /**
     * @dataProvider messageProvider
     *
     * @param $message
     */
    public function test_it_should_log_a_custom_log_level_with_x_death_max_count_reached($message)
    {
        $options = array(
            'x_death_max_count' => 1,
            'x_death_max_count_log_levels_map' => array(),
            'x_death_max_count_fail_log_levels_map' => array(
                '\BadMethodCallException' => LogLevel::CRITICAL,
            ),
        );

        $processorMock = $this->prophesize(ProcessorInterface::class);
        $processorMock
            ->process(Argument::exact($message), Argument::exact($options))
            ->willThrow('\BadMethodCallException')
            ->shouldBeCalledTimes(1);

        $callback = function () {
            return 'my_fake_return';
        };

        $logger = $this->prophesize(LoggerInterface::class);
        $logger
            ->log('critical', '[XDeathMaxCount] Max count reached. 4/1 attempts. Execute the configured callback.', Argument::cetera())
            ->shouldBeCalled();

        $processor = new XDeathMaxCountProcessor($processorMock->reveal(), 'good_queue', $callback, $logger->reveal());
        $this->assertEquals('my_fake_return', $processor->process($message, $options));
    }

    /**
     * @dataProvider messageProvider
     *
     * @param $message
     */
    public function test_it_should_log_a_custom_log_level_with_x_death_max_count_not_reached($message)
    {
        $this->expectException('\BadMethodCallException');

        $options = array(
            'x_death_max_count' => 10,
            'x_death_max_count_log_levels_map' => array(
                '\BadMethodCallException' => LogLevel::CRITICAL,
            ),
            'x_death_max_count_fail_log_levels_map' => array(),
        );

        $processorMock = $this->prophesize(ProcessorInterface::class);
        $processorMock
            ->process(Argument::exact($message), Argument::exact($options))
            ->willThrow('\BadMethodCallException')
            ->shouldBeCalledTimes(1);

        $callback = function () {
            return 'my_fake_return';
        };

        $logger = $this->prophesize(LoggerInterface::class);
        $logger
            ->log('critical', '[XDeathMaxCount] 4/10 attempts.', Argument::cetera())
            ->shouldBeCalled();

        $processor = new XDeathMaxCountProcessor($processorMock->reveal(), 'good_queue', $callback, $logger->reveal());
        $processor->process($message, $options);
    }

    /**
     * @dataProvider messageProvider
     *
     * @param $message
     */
    public function test_it_should_log_x_death_max_count_not_found($message)
    {
        $this->expectException('\BadMethodCallException');

        $options = array(
            'x_death_max_count' => 10,
            'x_death_max_count_log_levels_map' => array(),
            'x_death_max_count_fail_log_levels_map' => array(),
        );
        $processorMock = $this->prophesize(ProcessorInterface::class);
        $processorMock
            ->process(Argument::exact($message), Argument::exact($options))
            ->willThrow('\BadMethodCallException')
            ->shouldBeCalledTimes(1);

        $callback = function () {
            return 'my_fake_return';
        };

        $logger = $this->prophesize(LoggerInterface::class);
        $logger
            ->log('warning', '[XDeathMaxCount] No x-death header found for queue name "not_found_queue". Do nothing.', Argument::cetera())
            ->shouldBeCalled();

        $processor = new XDeathMaxCountProcessor($processorMock->reveal(), 'not_found_queue', $callback, $logger->reveal());
        $processor->process($message, $options);
    }
}
