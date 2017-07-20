<?php

namespace Swarrot\Processor\Insomniac;

use PHPUnit\Framework\TestCase;
use Swarrot\Broker\Message;

class InsomniacProcessorTest extends TestCase
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
