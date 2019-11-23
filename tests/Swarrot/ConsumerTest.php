<?php

namespace Swarrot\Tests;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Swarrot\Broker\Message;
use Swarrot\Broker\MessageProvider\MessageProviderInterface;
use Swarrot\Consumer;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Processor\InitializableInterface;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\SleepyInterface;
use Swarrot\Processor\TerminableInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConsumerTest extends TestCase
{
    public function test_it_is_initializable()
    {
        $provider = $this->prophesize(MessageProviderInterface::class);
        $processor = $this->prophesize(ProcessorInterface::class);

        $consumer = new Consumer($provider->reveal(), $processor->reveal());
        $this->assertInstanceOf(Consumer::class, $consumer);
    }

    public function test_it_returns_null_if_no_error_occurred()
    {
        $provider = $this->prophesize(MessageProviderInterface::class);
        $processor = $this->prophesize(ProcessorInterface::class);

        $message = new Message('body', [], 1);

        $provider->get()->willReturn($message);
        $provider->getQueueName()->willReturn('image_crop');
        $processor
            ->process(
                $message,
                [
                    'poll_interval' => '50000',
                    'queue' => 'image_crop',
                ]
            )
            ->willReturn(false)
        ;

        $consumer = new Consumer($provider->reveal(), $processor->reveal());
        $this->assertNull($consumer->consume());
    }

    public function test_it_call_processor_if_its_configurable()
    {
        $provider = $this->prophesize(MessageProviderInterface::class);
        $processor = $this->prophesize(ConfigurableInterface::class);

        $message = new Message('body', [], 1);

        $provider->get()->willReturn($message);
        $provider->getQueueName()->willReturn('');
        $processor->setDefaultOptions(
            Argument::type(OptionsResolver::class)
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
        $provider = $this->prophesize(MessageProviderInterface::class);
        $processor = $this->prophesize(InitializableInterface::class);

        $message = new Message('body', [], 1);

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
        $provider = $this->prophesize(MessageProviderInterface::class);
        $processor = $this->prophesize(TerminableInterface::class);

        $message = new Message('body', [], 1);

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
        $provider = $this->prophesize(MessageProviderInterface::class);
        $processor = $this->prophesize(SleepyInterface::class);

        $message = new Message('body', [], 1);

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
