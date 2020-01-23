<?php

namespace Swarrot\Tests\Processor\MemoryLimit;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Swarrot\Broker\Message;
use Swarrot\Processor\MemoryLimit\MemoryLimitProcessor;
use Swarrot\Processor\ProcessorInterface;

class MemoryLimitProcessorTest extends TestCase
{
    public function test_it_is_initializable_without_a_logger()
    {
        $processor = $this->prophesize(ProcessorInterface::class);

        $processor = new MemoryLimitProcessor($processor->reveal());
        $this->assertInstanceOf(MemoryLimitProcessor::class, $processor);
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $processor = new MemoryLimitProcessor($processor->reveal(), $logger->reveal());
        $this->assertInstanceOf(MemoryLimitProcessor::class, $processor);
    }

    public function test_delegate_processing()
    {
        $message = new Message('body', [], 1);

        $processor = $this->prophesize(ProcessorInterface::class);
        $processor->process($message, ['memory_limit' => null])->shouldBeCalledTimes(1)->willReturn(true);

        $logger = $this->prophesize(LoggerInterface::class);

        $processor = new MemoryLimitProcessor($processor->reveal(), $logger->reveal());

        $this->assertTrue($processor->process($message, ['memory_limit' => null]));
    }
}
