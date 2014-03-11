<?php

namespace Swarrot;

use Prophecy\Argument;
use Swarrot\Broker\MessageProviderInterface;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Processor\InitializableInterface;
use Swarrot\Processor\TerminableInterface;
use Swarrot\Processor\SleepyInterface;
use Swarrot\Broker\Message;

class ConsumerTest extends \PHPUnit_Framework_TestCase
{
    protected $prophet;

    protected function setUp()
    {
        $this->prophet = new \Prophecy\Prophet;
    }

    protected function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function test_it_is_initializable()
    {
        $provider  = $this->prophet->prophesize('Swarrot\Broker\MessageProviderInterface');
        $processor = $this->prophet->prophesize('Swarrot\Processor\ProcessorInterface');

        $consumer = new Consumer($provider->reveal(), $processor->reveal());
        $this->assertInstanceOf('Swarrot\Consumer', $consumer);
    }

    public function test_it_returns_null_if_no_error_occured()
    {
        $provider  = $this->prophet->prophesize('Swarrot\Broker\MessageProviderInterface');
        $processor = $this->prophet->prophesize('Swarrot\Processor\ProcessorInterface');

        $message = new Message(1, 'body');

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
        $provider  = $this->prophet->prophesize('Swarrot\Broker\MessageProviderInterface');
        $processor = $this->prophet->prophesize('Swarrot\Processor\ConfigurableInterface');

        $message = new Message(1, 'body');

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
        $provider  = $this->prophet->prophesize('Swarrot\Broker\MessageProviderInterface');
        $processor = $this->prophet->prophesize('Swarrot\Processor\InitializableInterface');

        $message = new Message(1, 'body');

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
        $provider  = $this->prophet->prophesize('Swarrot\Broker\MessageProviderInterface');
        $processor = $this->prophet->prophesize('Swarrot\Processor\TerminableInterface');

        $message = new Message(1, 'body');

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
        $provider  = $this->prophet->prophesize('Swarrot\Broker\MessageProviderInterface');
        $processor = $this->prophet->prophesize('Swarrot\Processor\SleepyInterface');

        $message = new Message(1, 'body');

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
