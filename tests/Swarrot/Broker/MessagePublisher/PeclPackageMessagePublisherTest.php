<?php

namespace Swarrot\Broker\MessagePublisher;

use Swarrot\Broker\Message;
use Prophecy\PhpUnit\ProphecyTestCase;
use Prophecy\Argument;

class PeclPackageMessagePublisherTest extends ProphecyTestCase
{
    protected function setUp()
    {
        if (!class_exists('AMQPConnection')) {
            $this->markTestSkipped('The AMQP extension is not available');
        }
        parent::setUp();
    }

    public function test_publish_with_valid_message()
    {
        $exchange = $this->prophesize('\AMQPExchange');
        $exchange
            ->publish(
                Argument::exact('body'),
                Argument::exact(null),
                Argument::exact(0),
                Argument::exact(array())
            )
            ->shouldBeCalledTimes(1)
        ;

        $provider = new PeclPackageMessagePublisher($exchange->reveal());
        $return = $provider->publish(
            new Message('body')
        );

        $this->assertNull($return);
    }

    public function test_publish_with_application_headers()
    {
        $exchange = $this->prophesize('\AMQPExchange');
        $exchange
            ->publish(
                Argument::exact('body'),
                Argument::exact(null),
                Argument::exact(0),
                Argument::exact(array(
                    'headers' => array(
                        'another_header' => 'another_value',
                        'string' => 'foobar',
                        'integer' => 42,
                        'array' => array('foo', 'bar')
                    )
                ))
            )
            ->shouldBeCalledTimes(1)
        ;
        $provider = new PeclPackageMessagePublisher($exchange->reveal());
        $return = $provider->publish(
            new Message('body', array(
                'application_headers' => array(
                    'string' => array('S', 'foobar'),
                    'integer' => array('I', 42),
                    'array' => array('A', array('foo', 'bar'))
                ),
                'headers' => array(
                    'another_header' => 'another_value'
                )
            ))
        );

        $this->assertNull($return);
    }
}
