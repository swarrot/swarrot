<?php

namespace Swarrot;

use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTestCase;
use Swarrot\Broker\Message;

class ConsumerTest extends ProphecyTestCase
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
        $processor->process(
            Argument::type('Swarrot\Broker\Message'),
            Argument::type('array')
        )->willReturn(false);

        $consumer = new Consumer($provider->reveal(), $processor->reveal());
        $this->assertNull($consumer->consume());
    }

    public function test_it_call_processor_if_its_configurable()
    {
        $provider  = $this->prophesize('Swarrot\Broker\MessageProvider\MessageProviderInterface');
        $processor = $this->prophesize('Swarrot\Processor\ConfigurableInterface');

        $message = new Message('body', array(), 1);

        $provider->get()->willReturn($message);
        $processor->setDefaultOptions(
            Argument::type('Symfony\Component\OptionsResolver\OptionsResolverInterface')
        )->willReturn(null);
        $processor->process(
            Argument::type('Swarrot\Broker\Message'),
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
        $processor->initialize(Argument::type('array'))->willReturn(null);
        $processor->process(
            Argument::type('Swarrot\Broker\Message'),
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
        $processor->terminate(Argument::type('array'))->willReturn(null);
        $processor->process(
            Argument::type('Swarrot\Broker\Message'),
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
        $processor->sleep(Argument::type('array'))->willReturn(null);
        $processor->process(
            Argument::type('Swarrot\Broker\Message'),
            Argument::type('array')
        )->willReturn(false);

        $consumer = new Consumer($provider->reveal(), $processor->reveal());
        $this->assertNull($consumer->consume());
    }
}
