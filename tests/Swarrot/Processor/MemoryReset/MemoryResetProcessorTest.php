<?php

namespace Swarrot\Tests\Processor\MemoryReset;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Swarrot\Broker\Message;
use Swarrot\Processor\MemoryReset\MemoryResetProcessor;
use Swarrot\Processor\ProcessorInterface;

class MemoryResetProcessorTest extends TestCase
{
    use ProphecyTrait;

    public function test_it_is_initializable()
    {
        $processor = $this->prophesize(ProcessorInterface::class);

        $processor = new MemoryResetProcessor($processor->reveal());
        $this->assertInstanceOf(MemoryResetProcessor::class, $processor);
    }

    public function test_delegate_processing()
    {
        $message = new Message('body', [], 1);

        $processor = $this->prophesize(ProcessorInterface::class);
        $processor->process($message, [])->shouldBeCalledTimes(1)->willReturn(true);

        $processor = new MemoryResetProcessor($processor->reveal());

        $this->assertTrue($processor->process($message, []));
    }
}
