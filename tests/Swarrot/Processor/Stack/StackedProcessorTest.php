<?php

namespace Swarrot\Processor\Stack;

use Swarrot\Broker\Message;
use Prophecy\Argument;

class StackedProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function test_it_is_initializable()
    {
        $p1  = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $p2  = $this->prophesize('Swarrot\Processor\InitializableInterface');
        $p3  = $this->prophesize('Swarrot\Processor\TerminableInterface');
        $p4  = $this->prophesize('Swarrot\Processor\SleepyInterface');

        $stackedProcessor = new StackedProcessor(
            $p1->reveal(), array(
                $p2->reveal(),
                $p3->reveal(),
                $p4->reveal()
            )
        );

        $this->assertInstanceOf('Swarrot\Processor\Stack\StackedProcessor', $stackedProcessor);
    }

    public function test_it_is_callable()
    {
        $p1  = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $p2  = $this->prophesize('Swarrot\Processor\InitializableInterface');
        $p3  = $this->prophesize('Swarrot\Processor\TerminableInterface');
        $p4  = $this->prophesize('Swarrot\Processor\SleepyInterface');

        $p2->initialize(Argument::type('array'))->willReturn(null);
        $p3->terminate(Argument::type('array'))->willReturn(null);
        $p4->sleep(Argument::type('array'))->willReturn(null);

        $p1->process(Argument::type('Swarrot\Broker\Message'), Argument::type('array'))->willReturn(null);
        $p2->process(Argument::type('Swarrot\Broker\Message'), Argument::type('array'))->willReturn(null);
        $p3->process(Argument::type('Swarrot\Broker\Message'), Argument::type('array'))->willReturn(null);
        $p4->process(Argument::type('Swarrot\Broker\Message'), Argument::type('array'))->willReturn(null);

        $stackedProcessor = new StackedProcessor(
            $p1->reveal(), array(
                $p2->reveal(),
                $p3->reveal(),
                $p4->reveal()
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
        $p1  = $this->prophesize('Swarrot\Processor\SleepyInterface');
        $p2  = $this->prophesize('Swarrot\Processor\SleepyInterface');
        $p3  = $this->prophesize('Swarrot\Processor\SleepyInterface');
        $p4  = $this->prophesize('Swarrot\Processor\SleepyInterface');

        $p2->sleep(Argument::type('array'))->willReturn(true);
        $p3->sleep(Argument::type('array'))->willReturn(true);
        $p4->sleep(Argument::type('array'))->willReturn(false);

        $stackedProcessor = new StackedProcessor(
            $p1->reveal(), array(
                $p2->reveal(),
                $p3->reveal(),
                $p4->reveal()
            )
        );

        $this->assertFalse($stackedProcessor->sleep(array()));
    }
}
