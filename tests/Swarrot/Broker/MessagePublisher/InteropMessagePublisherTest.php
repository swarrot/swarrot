<?php

namespace Swarrot\Broker\MessagePublisher;

use Swarrot\Broker\Message;
use Prophecy\Argument;

class InteropMessagePublisherTest extends \PHPUnit_Framework_TestCase
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
        $context = $this->prophesize('Interop\Queue\PsrContext');

        $this->assertInstanceOf(
            'Swarrot\Broker\MessagePublisher\MessagePublisherInterface',
            new InteropMessagePublisher($context->reveal(), 'aTopicName')
        );
    }

    public function test_publish_with_valid_message()
    {
        $topic = $this->prophesize('Interop\Queue\PsrTopic');

        $message = $this->prophesize('Interop\Queue\PsrMessage');

        $producer = $this->prophesize('Interop\Queue\PsrProducer');
        $producer
            ->send(Argument::exact($topic), Argument::exact($message))
            ->shouldBeCalledTimes(1)
        ;


        $context = $this->prophesize('Interop\Queue\PsrContext');
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
            ]
        ]));

        $this->assertNull($return);
    }
}
