<?php

namespace Swarrot\Tests\Processor\Insomniac;

use PHPUnit\Framework\TestCase;
use Swarrot\Broker\Message;
use Swarrot\Processor\Insomniac\InsomniacProcessor;
use Swarrot\Processor\ProcessorInterface;

class InsomniacProcessorTest extends TestCase
{
    public function test()
    {
        $message = new Message();
        $options = [];

        $decoratedProcessorProphecy = $this->prophesize(ProcessorInterface::class);
        $decoratedProcessorProphecy
            ->process($message, $options)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $processor = new InsomniacProcessor($decoratedProcessorProphecy->reveal());

        $this->assertTrue($processor->process($message, $options));

        $this->assertFalse($processor->sleep([]));
    }
}
