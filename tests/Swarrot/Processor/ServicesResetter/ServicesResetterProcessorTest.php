<?php

namespace Swarrot\Tests\Processor\ServicesResetter;

use PHPUnit\Framework\TestCase;
use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\ServicesResetter\ServicesResetterProcessor;
use Symfony\Contracts\Service\ResetInterface;

class ServicesResetterProcessorTest extends TestCase
{
    public function test()
    {
        $message = new Message();
        $options = [];

        $innerProcessorProphecy = $this->prophesize(ProcessorInterface::class);
        $innerProcessorProphecy->process($message, $options)->willReturn(true);

        $serviceResetterProphecy = $this->prophesize(ResetInterface::class);
        $serviceResetterProphecy->reset()->shouldBeCalled();

        $processor = new ServicesResetterProcessor($innerProcessorProphecy->reveal(), $serviceResetterProphecy->reveal());

        $this->assertEquals($processor->process($message, $options), true);
    }

    public function testWithException()
    {
        $message = new Message();
        $options = [];

        $innerProcessorProphecy = $this->prophesize(ProcessorInterface::class);
        $innerProcessorProphecy->process($message, $options)->willThrow(new \Exception('my_fake_message'));

        $serviceResetterProphecy = $this->prophesize(ResetInterface::class);
        $serviceResetterProphecy->reset()->shouldBeCalled();

        $processor = new ServicesResetterProcessor($innerProcessorProphecy->reveal(), $serviceResetterProphecy->reveal());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('my_fake_message');

        $processor->process($message, $options);
    }
}
