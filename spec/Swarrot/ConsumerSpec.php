<?php

namespace spec\Swarrot;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Swarrot\Broker\MessageProviderInterface;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Processor\InitializableInterface;
use Swarrot\Processor\TerminableInterface;
use Swarrot\Processor\SleepyInterface;
use Swarrot\Broker\Message;

class ConsumerSpec extends ObjectBehavior
{
    function it_is_initializable(MessageProviderInterface $provider, ProcessorInterface $processor)
    {
        $this->beConstructedWith($provider, $processor);
        $this->shouldHaveType('Swarrot\Consumer');
    }

    function it_returns_null_if_no_error_occured(MessageProviderInterface $provider, ProcessorInterface $processor, Message $message)
    {
        $provider->get()->willReturn($message);
        $processor->__invoke(
            Argument::type('Swarrot\Broker\Message'),
            Argument::type('array')
        )->willReturn(false);

        $this->beConstructedWith($provider, $processor);

        $this->consume()->shouldReturn(null);
    }

    function it_call_processor_if_its_configurable(MessageProviderInterface $provider, ConfigurableInterface $processor, Message $message)
    {
        $provider->get()->willReturn($message);
        $processor->setDefaultOptions(
            Argument::type('Symfony\Component\OptionsResolver\OptionsResolverInterface')
        )->willReturn(null);
        $processor->__invoke(
            Argument::type('Swarrot\Broker\Message'),
            Argument::type('array')
        )->willReturn(false);

        $this->beConstructedWith($provider, $processor);

        $this->consume()->shouldReturn(null);
    }

    function it_call_processor_if_its_initializable(MessageProviderInterface $provider, InitializableInterface $processor, Message $message)
    {
        $provider->get()->willReturn($message);
        $processor->initialize(Argument::type('array'))->willReturn(null);
        $processor->__invoke(
            Argument::type('Swarrot\Broker\Message'),
            Argument::type('array')
        )->willReturn(false);

        $this->beConstructedWith($provider, $processor);

        $this->consume()->shouldReturn(null);
    }

    function it_call_processor_if_its_terminable(MessageProviderInterface $provider, TerminableInterface $processor, Message $message)
    {
        $provider->get()->willReturn($message);
        $processor->terminate(Argument::type('array'))->willReturn(null);
        $processor->__invoke(
            Argument::type('Swarrot\Broker\Message'),
            Argument::type('array')
        )->willReturn(false);

        $this->beConstructedWith($provider, $processor);

        $this->consume()->shouldReturn(null);
    }

    function it_call_processor_if_its_Sleepy(MessageProviderInterface $provider, SleepyInterface $processor, Message $message)
    {
        $provider->get()->willReturn($message);
        $processor->sleep(Argument::type('array'))->willReturn(null);
        $processor->__invoke(
            Argument::type('Swarrot\Broker\Message'),
            Argument::type('array')
        )->willReturn(false);

        $this->beConstructedWith($provider, $processor);

        $this->consume()->shouldReturn(null);
    }
}
