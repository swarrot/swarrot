<?php

namespace Swarrot\Tests\Processor\Stack;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Swarrot\Broker\Message;
use Swarrot\Processor\InitializableInterface;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\SleepyInterface;
use Swarrot\Processor\Stack\StackedProcessor;
use Swarrot\Processor\TerminableInterface;

class StackedProcessorTest extends TestCase
{
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

        $p2->initialize(Argument::type('array'))->willReturn(null);
        $p3->terminate(Argument::type('array'))->willReturn(null);
        $p4->sleep(Argument::type('array'))->willReturn(null);

        $p1->process(Argument::type(Message::class), Argument::type('array'))->willReturn(null);
        $p2->process(Argument::type(Message::class), Argument::type('array'))->willReturn(null);
        $p3->process(Argument::type(Message::class), Argument::type('array'))->willReturn(null);
        $p4->process(Argument::type(Message::class), Argument::type('array'))->willReturn(null);

        $stackedProcessor = new StackedProcessor(
            $p1->reveal(), [
                $p2->reveal(),
                $p3->reveal(),
                $p4->reveal(),
            ]
        );

        $this->assertNull($stackedProcessor->initialize([]));
        $this->assertNull($stackedProcessor->terminate([]));
        $this->assertTrue($stackedProcessor->sleep([]));

        $this->assertNull($stackedProcessor->process(
            new Message('body', [], 1),
            []
        ));
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

    /**
     * @group legacy
     * @expectedDeprecation Using "Swarrot\Processor\Stack\StackedProcessor" without a ProcessorInterface have been deprecated since Swarrot 3.7
     */
    public function test_without_processor_interface()
    {
        $stackedProcessor = new StackedProcessor(function () {}, []);
    }
}
