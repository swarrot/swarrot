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

    public function test_get_with_messages_in_queue_return_message()
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
//
//    public function test_get_without_messages_in_queue_return_null()
//    {
//        $queue = $this->prophesize('Interop\Queue\PsrQueue');
//        $consumer = $this->prophesize('Interop\Queue\PsrConsumer');
//        $consumer
//            ->receive(Argument::exact(1234))
//            ->shouldBeCalledTimes(1)
//            ->willReturn(null)
//        ;
//
//        $context = $this->prophesize('Interop\Queue\PsrContext');
//        $context
//            ->createQueue(Argument::exact("aQueueName"))
//            ->willReturn($queue)
//        ;
//        $context
//            ->createConsumer(Argument::exact($queue))
//            ->willReturn($consumer)
//        ;
//
//        $provider = new InteropMessageProvider($context->reveal(), 'aQueueName', 1234);
//
//        $message = $provider->get();
//
//        $this->assertNull($message);
//    }
//
//    public function test_ack_got_message()
//    {
//        $message = $this->prophesize('Interop\Queue\PsrMessage');
//
//        $queue = $this->prophesize('Interop\Queue\PsrQueue');
//        $consumer = $this->prophesize('Interop\Queue\PsrConsumer');
//        $consumer
//            ->receive(Argument::exact(1234))
//            ->shouldBeCalledTimes(1)
//            ->willReturn($message)
//        ;
//        $consumer
//            ->acknowledge(Argument::exact($message))
//            ->shouldBeCalledTimes(1)
//        ;
//
//        $context = $this->prophesize('Interop\Queue\PsrContext');
//        $context
//            ->createQueue(Argument::exact("aQueueName"))
//            ->willReturn($queue)
//        ;
//        $context
//            ->createConsumer(Argument::exact($queue))
//            ->willReturn($consumer)
//        ;
//
//        $provider = new InteropMessageProvider($context->reveal(), 'aQueueName', 1234);
//
//        $swarrotMessage = $provider->get();
//
//        //guard
//        $this->assertInstanceOf('Swarrot\Broker\Message', $swarrotMessage);
//
//        $provider->ack($swarrotMessage);
//
//        // should do nothing if called second time
//        $provider->ack($swarrotMessage);
//    }
//
//    public function test_nack_got_message()
//    {
//        $message = $this->prophesize('Interop\Queue\PsrMessage');
//
//        $queue = $this->prophesize('Interop\Queue\PsrQueue');
//        $consumer = $this->prophesize('Interop\Queue\PsrConsumer');
//        $consumer
//            ->receive(Argument::exact(1234))
//            ->shouldBeCalledTimes(1)
//            ->willReturn($message)
//        ;
//        $consumer
//            ->reject(Argument::exact($message), Argument::exact(true))
//            ->shouldBeCalledTimes(1)
//        ;
//
//        $context = $this->prophesize('Interop\Queue\PsrContext');
//        $context
//            ->createQueue(Argument::exact("aQueueName"))
//            ->willReturn($queue)
//        ;
//        $context
//            ->createConsumer(Argument::exact($queue))
//            ->willReturn($consumer)
//        ;
//
//        $provider = new InteropMessageProvider($context->reveal(), 'aQueueName', 1234);
//
//        $swarrotMessage = $provider->get();
//
//        //guard
//        $this->assertInstanceOf('Swarrot\Broker\Message', $swarrotMessage);
//
//        $provider->nack($swarrotMessage, true);
//
//        // should do nothing if called second time
//        $provider->nack($swarrotMessage, true);
//    }
//
//    public function test_get_name()
//    {
//        $queue = $this->prophesize('Interop\Queue\PsrQueue');
//        $queue
//            ->getQueueName()
//            ->shouldBeCalledTimes(1)
//            ->willReturn('theQueueName')
//        ;
//
//        $consumer = $this->prophesize('Interop\Queue\PsrConsumer');
//
//        $context = $this->prophesize('Interop\Queue\PsrContext');
//        $context
//            ->createQueue(Argument::exact("theQueueName"))
//            ->willReturn($queue)
//        ;
//        $context
//            ->createConsumer(Argument::exact($queue))
//            ->willReturn($consumer)
//        ;
//
//        $provider = new InteropMessageProvider($context->reveal(), 'theQueueName');
//
//        $this->assertEquals('theQueueName', $provider->getQueueName());
//    }
}
