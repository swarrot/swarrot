<?php

namespace Swarrot\Tests\Broker\MessagePublisher;

use Interop\Queue\PsrContext;
use Interop\Queue\PsrMessage;
use Interop\Queue\PsrProducer;
use Interop\Queue\PsrTopic;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Swarrot\Broker\Message;
use Swarrot\Broker\MessagePublisher\InteropMessagePublisher;
use Swarrot\Broker\MessagePublisher\MessagePublisherInterface;

class InteropMessagePublisherTest extends TestCase
{
    protected function setUp(): void
    {
        if (!interface_exists(PsrContext::class)) {
            $this->markTestSkipped('The queue-interop package is not available');
        }

        parent::setUp();
    }

    /**
     * @group legacy
     * @expectedDeprecation "Swarrot\Broker\MessagePublisher\InteropMessagePublisher" have been deprecated since Swarrot 3.7
     */
    public function testInstance()
    {
        $context = $this->prophesize(PsrContext::class);

        $this->assertInstanceOf(
            MessagePublisherInterface::class,
            new InteropMessagePublisher($context->reveal(), 'aTopicName')
        );
    }

    /**
     * @group legacy
     * @expectedDeprecation "Swarrot\Broker\MessagePublisher\InteropMessagePublisher" have been deprecated since Swarrot 3.7
     */
    public function test_publish_with_valid_message()
    {
        $topic = $this->prophesize(PsrTopic::class);

        $message = $this->prophesize(PsrMessage::class);

        $producer = $this->prophesize(PsrProducer::class);
        $producer
            ->send(Argument::exact($topic), Argument::exact($message))
            ->shouldBeCalledTimes(1)
        ;

        $context = $this->prophesize(PsrContext::class);
        $context
            ->createTopic(Argument::exact('theTopicName'))
            ->shouldBeCalledTimes(1)
            ->willReturn($topic)
        ;
        $context
            ->createProducer()
            ->shouldBeCalledTimes(1)
            ->willReturn($producer)
        ;
        $context
            ->createMessage(
                Argument::exact('theBody'),
                Argument::exact(['fooHeader' => 'fooHeaderVal']),
                Argument::exact(['fooProp' => 'fooPropVal'])
            )
            ->shouldBeCalledTimes(1)
            ->willReturn($message)
        ;

        $publisher = new InteropMessagePublisher($context->reveal(), 'theTopicName');
        $return = $publisher->publish(new Message('theBody', [
            'fooProp' => 'fooPropVal',
            'headers' => [
                'fooHeader' => 'fooHeaderVal',
            ],
        ]));

        $this->assertNull($return);
    }
}
