<?php

namespace Swarrot\Processor\Stack;

use Interop\Queue\PsrProcessor;
use Prophecy\Argument;
use Swarrot\Processor\InteropProxyProcessor;

class InteropProxyProcessorTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!interface_exists('Interop\Queue\PsrContext')) {
            $this->markTestSkipped('The queue-interop package is not available');
        }

        parent::setUp();
    }

    public function testInstance()
    {
        $swarrotProcessor = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $this->assertInstanceOf(
            'Interop\Queue\PsrProcessor',
            new InteropProxyProcessor($swarrotProcessor->reveal())
        );
    }

    public function test_converts_message_and_proxy_it_to_processor()
    {
        $message = $this->prophesize('Interop\Queue\PsrMessage');
        $message
            ->getBody()
            ->shouldBeCalledTimes(1)
            ->willReturn('theBody')
        ;
        $message
            ->getProperties()
            ->shouldBeCalledTimes(1)
            ->willReturn(['fooProp' => 'fooPropVal'])
        ;
        $message
            ->getHeaders()
            ->shouldBeCalledTimes(1)
            ->willReturn(['fooHeader' => 'fooHeaderVal'])
        ;

        $swarrotProcessor = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $swarrotProcessor
            ->process(
                Argument::that(function($message) {
                    $this->assertInstanceOf('Swarrot\Broker\Message', $message);
                    $this->assertSame('theBody', $message->getBody());
                    $this->assertSame([
                        'fooHeader' => 'fooHeaderVal',
                        'headers' => ['fooProp' => 'fooPropVal'],
                    ], $message->getProperties());

                    return true;
                }),
                Argument::exact(['fooOpt' => 'fooOptVal'])
            )
            ->shouldBeCalledTimes(1)
        ;

        $context = $this->prophesize('Interop\Queue\PsrContext');

        $processor = new InteropProxyProcessor($swarrotProcessor->reveal(), ['fooOpt' => 'fooOptVal']);

        $result = $processor->process($message->reveal(), $context->reveal());

        $this->assertSame(PsrProcessor::ACK, $result);


    }
}
