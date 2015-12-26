<?php

namespace Swarrot\Processor\Insomniac;

use Swarrot\Broker\Message;

class InsomniacProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $message = new Message();
        $options = [];

        $decoratedProcessorProphecy = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $decoratedProcessorProphecy
            ->process($message, $options)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $processor = new InsomniacProcessor($decoratedProcessorProphecy->reveal());

        $this->assertEquals(true, $processor->process($message, $options));

        $this->assertFalse($processor->sleep([]));
    }
}
