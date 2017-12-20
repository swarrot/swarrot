<?php

namespace Swarrot\Tests\Broker\MessageProvider;

use Interop\Queue\PsrConsumer;
use Interop\Queue\PsrContext;
use Interop\Queue\PsrMessage;
use Interop\Queue\PsrQueue;
use Prophecy\Argument;
use PHPUnit\Framework\TestCase;
use Swarrot\Broker\Message;
use Swarrot\Broker\MessageProvider\InteropMessageProvider;
use Swarrot\Broker\MessageProvider\MessageProviderInterface;

class InteropMessageProviderTest extends TestCase
{
    protected function setUp()
    {
        if (!interface_exists(PsrContext::class)) {
            $this->markTestSkipped('The queue-interop package is not available');
        }

        parent::setUp();
    }

    public function testInstance()
    {
        $queue = $this->prophesize(PsrQueue::class);
        $consumer = $this->prophesize(PsrConsumer::class);

        $context = $this->prophesize(PsrContext::class);
        $context
            ->createQueue(Argument::exact('aQueueName'))
            ->willReturn($queue)
        ;
        $context
            ->createConsumer(Argument::exact($queue))
            ->willReturn($consumer)
        ;

        $this->assertInstanceOf(
            MessageProviderInterface::class,
            new InteropMessageProvider($context->reveal(), 'aQueueName')
        );
    }

    public function test_get_with_messages_in_queue_return_message()
    {
        $message = $this->prophesize(PsrMessage::class);
        $message
            ->getBody()
            ->shouldBeCalledTimes(1)
            ->willReturn('theBody')
        ;
        $message
            ->getMessageId()
            ->shouldBeCalledTimes(1)
            ->willReturn(null)
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

        $queue = $this->prophesize(PsrQueue::class);
        $consumer = $this->prophesize(PsrConsumer::class);
        $consumer
            ->receive(Argument::exact(1234))
            ->shouldBeCalledTimes(1)
            ->willReturn($message)
        ;

        $context = $this->prophesize(PsrContext::class);
        $context
            ->createQueue(Argument::exact('aQueueName'))
            ->willReturn($queue)
        ;
        $context
            ->createConsumer(Argument::exact($queue))
            ->willReturn($consumer)
        ;

        $provider = new InteropMessageProvider($context->reveal(), 'aQueueName', 1234);

        $message = $provider->get();

        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame('theBody', $message->getBody());
        $this->assertSame([
            'fooHeader' => 'fooHeaderVal',
            'headers' => ['fooProp' => 'fooPropVal'],
        ], $message->getProperties());
    }

    public function test_get_without_messages_in_queue_return_null()
    {
        $queue = $this->prophesize(PsrQueue::class);
        $consumer = $this->prophesize(PsrConsumer::class);
        $consumer
            ->receive(Argument::exact(1234))
            ->shouldBeCalledTimes(1)
            ->willReturn(null)
        ;

        $context = $this->prophesize(PsrContext::class);
        $context
            ->createQueue(Argument::exact('aQueueName'))
            ->willReturn($queue)
        ;
        $context
            ->createConsumer(Argument::exact($queue))
            ->willReturn($consumer)
        ;

        $provider = new InteropMessageProvider($context->reveal(), 'aQueueName', 1234);

        $message = $provider->get();

        $this->assertNull($message);
    }

    public function test_ack_got_message()
    {
        $message = $this->prophesize(PsrMessage::class);

        $queue = $this->prophesize(PsrQueue::class);
        $consumer = $this->prophesize(PsrConsumer::class);
        $consumer
            ->receive(Argument::exact(1234))
            ->shouldBeCalledTimes(1)
            ->willReturn($message)
        ;
        $consumer
            ->acknowledge(Argument::exact($message))
            ->shouldBeCalledTimes(1)
        ;

        $context = $this->prophesize(PsrContext::class);
        $context
            ->createQueue(Argument::exact('aQueueName'))
            ->willReturn($queue)
        ;
        $context
            ->createConsumer(Argument::exact($queue))
            ->willReturn($consumer)
        ;

        $provider = new InteropMessageProvider($context->reveal(), 'aQueueName', 1234);

        $swarrotMessage = $provider->get();

        //guard
        $this->assertInstanceOf('Swarrot\Broker\Message', $swarrotMessage);

        $provider->ack($swarrotMessage);

        // should do nothing if called second time
        $provider->ack($swarrotMessage);
    }

    public function test_nack_got_message()
    {
        $message = $this->prophesize(PsrMessage::class);

        $queue = $this->prophesize(PsrQueue::class);
        $consumer = $this->prophesize(PsrConsumer::class);
        $consumer
            ->receive(Argument::exact(1234))
            ->shouldBeCalledTimes(1)
            ->willReturn($message)
        ;
        $consumer
            ->reject(Argument::exact($message), Argument::exact(true))
            ->shouldBeCalledTimes(1)
        ;

        $context = $this->prophesize(PsrContext::class);
        $context
            ->createQueue(Argument::exact('aQueueName'))
            ->willReturn($queue)
        ;
        $context
            ->createConsumer(Argument::exact($queue))
            ->willReturn($consumer)
        ;

        $provider = new InteropMessageProvider($context->reveal(), 'aQueueName', 1234);

        $swarrotMessage = $provider->get();

        //guard
        $this->assertInstanceOf('Swarrot\Broker\Message', $swarrotMessage);

        $provider->nack($swarrotMessage, true);

        // should do nothing if called second time
        $provider->nack($swarrotMessage, true);
    }

    public function test_get_name()
    {
        $queue = $this->prophesize(PsrQueue::class);
        $queue
            ->getQueueName()
            ->shouldBeCalledTimes(1)
            ->willReturn('theQueueName')
        ;

        $consumer = $this->prophesize(PsrConsumer::class);

        $context = $this->prophesize(PsrContext::class);
        $context
            ->createQueue(Argument::exact('theQueueName'))
            ->willReturn($queue)
        ;
        $context
            ->createConsumer(Argument::exact($queue))
            ->willReturn($consumer)
        ;

        $provider = new InteropMessageProvider($context->reveal(), 'theQueueName');

        $this->assertEquals('theQueueName', $provider->getQueueName());
    }
}
