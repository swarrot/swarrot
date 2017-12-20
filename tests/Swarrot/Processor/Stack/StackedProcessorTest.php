<?php

namespace Swarrot\Tests\Processor\Stack;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\InitializableInterface;
use Swarrot\Processor\TerminableInterface;
use Swarrot\Processor\SleepyInterface;
use Swarrot\Processor\Stack\StackedProcessor;

class StackedProcessorTest extends TestCase
{
    public function test_it_is_initializable()
    {
        $p1 = $this->prophesize(ProcessorInterface::class);
        $p2 = $this->prophesize(InitializableInterface::class);
        $p3 = $this->prophesize(TerminableInterface::class);
        $p4 = $this->prophesize(SleepyInterface::class);

        $stackedProcessor = new StackedProcessor(
            $p1->reveal(), array(
                $p2->reveal(),
                $p3->reveal(),
                $p4->reveal(),
            )
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
            $p1->reveal(), array(
                $p2->reveal(),
                $p3->reveal(),
                $p4->reveal(),
            )
        );

        $this->assertNull($stackedProcessor->initialize(array()));
        $this->assertNull($stackedProcessor->terminate(array()));
        $this->assertTrue($stackedProcessor->sleep(array()));

        $this->assertNull($stackedProcessor->process(
            new Message('body', array(), 1),
            array()
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
            $p1->reveal(), array(
                $p2->reveal(),
                $p3->reveal(),
                $p4->reveal(),
            )
        );

        $this->assertFalse($stackedProcessor->sleep(array()));
    }
}
