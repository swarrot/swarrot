<?php

namespace Swarrot\Tests\Processor\MaxExecutionTime;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use Swarrot\Broker\Message;
use Swarrot\Processor\MaxExecutionTime\MaxExecutionTimeProcessor;
use Swarrot\Processor\ProcessorInterface;

class MaxExecutionTimeProcessorTest extends TestCase
{
    use ProphecyTrait;

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
        $message = new Message('body', [], 1);

        $maxExecutionTime = 1;
        $processor = $this->prophesize(ProcessorInterface::class);
        $processor->process($message, ['max_execution_time' => $maxExecutionTime])->willReturn(true);

        $logger = $this->prophesize(LoggerInterface::class);
        $logger->info('[MaxExecutionTime] Max execution time has been reached', [
            'max_execution_time' => $maxExecutionTime,
            'swarrot_processor' => 'max_execution_time',
        ])->shouldBeCalledTimes(1);

        $processor = new MaxExecutionTimeProcessor($processor->reveal(), $logger->reveal());

        $processor->initialize([]);

        $startTime = microtime(true);
        while (true) {
            if (false === $processor->process($message, ['max_execution_time' => $maxExecutionTime])) {
                break;
            }
        }

        $this->assertTrue(microtime(true) - $startTime > $maxExecutionTime);
    }

    public function test_it_stops_after_slow_processing()
    {
        $message = new Message('body', [], 1);

        $maxExecutionTime = 1;
        $innerProcessor = $this->prophesize(ProcessorInterface::class);
        $innerProcessor->process($message, ['max_execution_time' => $maxExecutionTime])->willReturn(true);

        $logger = $this->prophesize(LoggerInterface::class);
        $logger->info('[MaxExecutionTime] Max execution time has been reached', [
            'max_execution_time' => $maxExecutionTime,
            'swarrot_processor' => 'max_execution_time',
        ])->shouldBeCalledTimes(1);

        $processor = new MaxExecutionTimeProcessor($innerProcessor->reveal(), $logger->reveal());

        $processor->initialize([]);

        sleep(1);

        $this->assertFalse($processor->process($message, ['max_execution_time' => $maxExecutionTime]));
    }
}
