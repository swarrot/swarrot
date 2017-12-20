<?php

namespace Swarrot\Tests\Processor\MemoryLimit;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\MemoryLimit\MemoryLimitProcessor;

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
        $logger    = $this->prophesize(LoggerInterface::class);

        $processor = new MemoryLimitProcessor($processor->reveal(), $logger->reveal());
        $this->assertInstanceOf(MemoryLimitProcessor::class, $processor);
    }

    public function test_delegate_processing()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $processor->process(
            Argument::type(Message::class),
            Argument::exact(array(
                'memory_limit' => null,
            ))
        )
        ->shouldBeCalledTimes(1);

        $logger = $this->prophesize(LoggerInterface::class);

        $message = new Message('body', array(), 1);
        $processor = new MemoryLimitProcessor(
            $processor->reveal(),
            $logger->reveal()
        );

        // Process
        $this->assertNull($processor->process($message, array('memory_limit' => null)));
    }
}
