<?php

namespace Swarrot\Tests\Processor\Stack;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Swarrot\Broker\Message;
use Swarrot\Processor\InitializableInterface;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\SleepyInterface;
use Swarrot\Processor\Stack\StackedProcessor;
use Swarrot\Processor\TerminableInterface;

class StackedProcessorTest extends TestCase
{
    use ProphecyTrait;

    public function test_it_is_initializable()
    {
        $p1 = $this->prophesize(ProcessorInterface::class);
        $p2 = $this->prophesize(InitializableInterface::class);
        $p3 = $this->prophesize(TerminableInterface::class);
        $p4 = $this->prophesize(SleepyInterface::class);

        $stackedProcessor = new StackedProcessor(
            $p1->reveal(), [
                $p2->reveal(),
                $p3->reveal(),
                $p4->reveal(),
            ]
        );

        $this->assertInstanceOf(StackedProcessor::class, $stackedProcessor);
    }

    public function test_it_is_callable()
    {
        $p1 = $this->prophesize(ProcessorInterface::class);
        $p2 = $this->prophesize(InitializableInterface::class);
        $p3 = $this->prophesize(TerminableInterface::class);
        $p4 = $this->prophesize(SleepyInterface::class);

        $p2->initialize(Argument::type('array'))->shouldBeCalledTimes(1);
        $p3->terminate(Argument::type('array'))->shouldBeCalledTimes(1);
        $p4->sleep(Argument::type('array'))->shouldBeCalledTimes(1);

        $message = new Message('body', [], 1);

        $p1->process($message, [])->willReturn(true);
        $p2->process($message, [])->willReturn(true);
        $p3->process($message, [])->willReturn(true);
        $p4->process($message, [])->willReturn(true);

        $stackedProcessor = new StackedProcessor($p1->reveal(), [
            $p2->reveal(),
            $p3->reveal(),
            $p4->reveal(),
        ]);

        $stackedProcessor->initialize([]);
        $stackedProcessor->terminate([]);
        $this->assertFalse($stackedProcessor->sleep([]));

        $this->assertTrue($stackedProcessor->process($message, []));
    }

    public function test_sleep_return_false_if_at_least_a_processor_return_false()
    {
        $p1 = $this->prophesize(SleepyInterface::class);
        $p2 = $this->prophesize(SleepyInterface::class);
        $p3 = $this->prophesize(SleepyInterface::class);
        $p4 = $this->prophesize(SleepyInterface::class);

        $p2->sleep(Argument::type('array'))->willReturn(true);
        $p3->sleep(Argument::type('array'))->willReturn(true);
        $p4->sleep(Argument::type('array'))->willReturn(false);

        $stackedProcessor = new StackedProcessor(
            $p1->reveal(), [
                $p2->reveal(),
                $p3->reveal(),
                $p4->reveal(),
            ]
        );

        $this->assertFalse($stackedProcessor->sleep([]));
    }
}
