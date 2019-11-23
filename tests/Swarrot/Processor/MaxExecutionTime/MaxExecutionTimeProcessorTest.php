<?php

namespace Swarrot\Tests\Processor\MaxExecutionTime;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Swarrot\Broker\Message;
use Swarrot\Processor\MaxExecutionTime\MaxExecutionTimeProcessor;
use Swarrot\Processor\ProcessorInterface;

class MaxExecutionTimeProcessorTest extends TestCase
{
    public function test_it_is_initializable_without_a_logger()
    {
        $processor = $this->prophesize(ProcessorInterface::class);

        $processor = new MaxExecutionTimeProcessor($processor->reveal());
        $this->assertInstanceOf(MaxExecutionTimeProcessor::class, $processor);
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $processor = new MaxExecutionTimeProcessor($processor->reveal(), $logger->reveal());
        $this->assertInstanceOf(MaxExecutionTimeProcessor::class, $processor);
    }

    public function test_count_default_messages_processed()
    {
        $maxExecutionTime = 1;
        $processor = $this->prophesize(ProcessorInterface::class);
        $processor->process(
            Argument::type(Message::class),
            Argument::exact([
                'max_execution_time' => $maxExecutionTime,
            ])
        );

        $logger = $this->prophesize(LoggerInterface::class);
        $logger->info(
            Argument::exact('[MaxExecutionTime] Max execution time has been reached'),
            Argument::exact([
                'max_execution_time' => $maxExecutionTime,
                'swarrot_processor' => 'max_execution_time',
            ])
        )
        ->shouldBeCalledTimes(1);

        $message = new Message('body', [], 1);
        $processor = new MaxExecutionTimeProcessor(
            $processor->reveal(),
            $logger->reveal()
        );

        // Should be called by the Consumer
        $processor->initialize([]);

        $startTime = microtime(true);
        while (true) {
            if (false === $processor->process($message, ['max_execution_time' => $maxExecutionTime])) {
                break;
            }
        }

        $this->assertTrue(microtime(true) - $startTime > $maxExecutionTime);
    }
}
