<?php

namespace Swarrot\Processor\Insomniac;

use Prophecy\PhpUnit\ProphecyTestCase;
use Swarrot\Broker\Message;

class InsomniacProcessorTest extends ProphecyTestCase
{
    public function test()
    {
        $message = new Message();
        $options = array();

        $decoratedProcessorProphecy = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $decoratedProcessorProphecy
            ->process($message, $options)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $processor = new InsomniacProcessor($decoratedProcessorProphecy->reveal());

        $this->assertEquals(true, $processor->process($message, $options));

        $this->assertFalse($processor->sleep(array()));
    }
}
