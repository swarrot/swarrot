<?php

namespace Swarrot;

use Prophecy\Argument;
use Swarrot\Broker\Message;

class ConsumerTest extends \PHPUnit_Framework_TestCase
{
    public function test_it_is_initializable()
    {
        $provider  = $this->prophesize('Swarrot\Broker\MessageProvider\MessageProviderInterface');
        $processor = $this->prophesize('Swarrot\Processor\ProcessorInterface');

        $consumer = new Consumer($provider->reveal(), $processor->reveal());
        $this->assertInstanceOf('Swarrot\Consumer', $consumer);
    }

    public function test_it_returns_null_if_no_error_occurred()
    {
        $provider  = $this->prophesize('Swarrot\Broker\MessageProvider\MessageProviderInterface');
        $processor = $this->prophesize('Swarrot\Processor\ProcessorInterface');

        $message = new Message('body', array(), 1);

        $provider->get()->willReturn($message);
        $provider->getQueueName()->willReturn('image_crop');
        $processor
            ->process(
                $message,
                [
                    'poll_interval' => '50000',
                    'queue'         => 'image_crop',
                ]
            )
            ->willReturn(false)
        ;

        $consumer = new Consumer($provider->reveal(), $processor->reveal());
        $this->assertNull($consumer->consume());
    }

    public function test_it_call_processor_if_its_configurable()
    {
        $provider  = $this->prophesize('Swarrot\Broker\MessageProvider\MessageProviderInterface');
        $processor = $this->prophesize('Swarrot\Processor\ConfigurableInterface');

        $message = new Message('body', array(), 1);

        $provider->get()->willReturn($message);
        $provider->getQueueName()->willReturn('');
        $processor->setDefaultOptions(
            Argument::type('Symfony\Component\OptionsResolver\OptionsResolver')
        )->willReturn(null);
        $processor->process(
            $message,
            Argument::type('array')
        )->willReturn(false);

        $consumer = new Consumer($provider->reveal(), $processor->reveal());
        $this->assertNull($consumer->consume());
    }

    public function test_it_call_processor_if_its_initializable()
    {
        $provider  = $this->prophesize('Swarrot\Broker\MessageProvider\MessageProviderInterface');
        $processor = $this->prophesize('Swarrot\Processor\InitializableInterface');

        $message = new Message('body', array(), 1);

        $provider->get()->willReturn($message);
        $provider->getQueueName()->willReturn('');
        $processor->initialize(Argument::type('array'))->willReturn(null);
        $processor->process(
            $message,
            Argument::type('array')
        )->willReturn(false);

        $consumer = new Consumer($provider->reveal(), $processor->reveal());
        $this->assertNull($consumer->consume());
    }

    public function test_it_call_processor_if_its_terminable()
    {
        $provider  = $this->prophesize('Swarrot\Broker\MessageProvider\MessageProviderInterface');
        $processor = $this->prophesize('Swarrot\Processor\TerminableInterface');

        $message = new Message('body', array(), 1);

        $provider->get()->willReturn($message);
        $provider->getQueueName()->willReturn('');
        $processor->terminate(Argument::type('array'))->willReturn(null);
        $processor->process(
            $message,
            Argument::type('array')
        )->willReturn(false);

        $consumer = new Consumer($provider->reveal(), $processor->reveal());
        $this->assertNull($consumer->consume());
    }

    public function test_it_call_processor_if_its_Sleepy()
    {
        $provider  = $this->prophesize('Swarrot\Broker\MessageProvider\MessageProviderInterface');
        $processor = $this->prophesize('Swarrot\Processor\SleepyInterface');

        $message = new Message('body', array(), 1);

        $provider->get()->willReturn($message);
        $provider->getQueueName()->willReturn('');
        $processor->sleep(Argument::type('array'))->willReturn(null);
        $processor->process(
            $message,
            Argument::type('array')
        )->willReturn(false);

        $consumer = new Consumer($provider->reveal(), $processor->reveal());
        $this->assertNull($consumer->consume());
    }
}
