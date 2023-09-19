<?php

namespace Swarrot\Tests;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
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
    use ProphecyTrait;

    public function test_it_is_initializable()
    {
        $provider = $this->prophesize(MessageProviderInterface::class);
        $processor = $this->prophesize(ProcessorInterface::class);

        $consumer = new Consumer($provider->reveal(), $processor->reveal());
        $this->assertInstanceOf(Consumer::class, $consumer);
    }

    public function test_it_returns_null_if_no_error_occurred()
    {
        $message = new Message('body', [], 1);

        $provider = $this->prophesize(MessageProviderInterface::class);
        $provider->get()->shouldBeCalledTimes(1)->willReturn($message);
        $provider->getQueueName()->shouldBeCalledTimes(1)->willReturn('my_queue');

        $processor = $this->prophesize(ProcessorInterface::class);
        $processor
            ->process(
                $message,
                [
                    'poll_interval' => '50000',
                    'queue' => 'my_queue',
                ]
            )
            ->shouldBeCalledTimes(1)
            ->willReturn(false)
        ;

        $consumer = new Consumer($provider->reveal(), $processor->reveal());
        $consumer->consume();
    }

    public function test_it_call_processor_if_its_configurable()
    {
        $message = new Message('body', [], 1);

        $provider = $this->prophesize(MessageProviderInterface::class);
        $provider->get()->shouldBeCalledTimes(1)->willReturn($message);
        $provider->getQueueName()->shouldBeCalledTimes(1)->willReturn('');

        $processor = $this->prophesize(ConfigurableInterface::class);
        $processor->setDefaultOptions(Argument::type(OptionsResolver::class))->shouldBeCalledTimes(1);
        $processor->process($message, Argument::type('array'))->shouldBeCalledTimes(1)->willReturn(false);

        $consumer = new Consumer($provider->reveal(), $processor->reveal());
        $consumer->consume();
    }

    public function test_it_call_processor_if_its_initializable()
    {
        $message = new Message('body', [], 1);

        $provider = $this->prophesize(MessageProviderInterface::class);
        $provider->get()->shouldBeCalledTimes(1)->willReturn($message);
        $provider->getQueueName()->shouldBeCalledTimes(1)->willReturn('');

        $processor = $this->prophesize(InitializableInterface::class);
        $processor->initialize(Argument::type('array'))->shouldBeCalledTimes(1);
        $processor->process($message, Argument::type('array'))->shouldBeCalledTimes(1)->willReturn(false);

        $consumer = new Consumer($provider->reveal(), $processor->reveal());
        $consumer->consume();
    }

    public function test_it_call_processor_if_its_terminable()
    {
        $message = new Message('body', [], 1);

        $provider = $this->prophesize(MessageProviderInterface::class);
        $provider->get()->shouldBeCalledTimes(1)->willReturn($message);
        $provider->getQueueName()->shouldBeCalledTimes(1)->willReturn('');

        $processor = $this->prophesize(TerminableInterface::class);
        $processor->terminate(Argument::type('array'))->shouldBeCalledTimes(1);
        $processor->process($message, Argument::type('array'))->shouldBeCalledTimes(1)->willReturn(false);

        $consumer = new Consumer($provider->reveal(), $processor->reveal());
        $consumer->consume();
    }

    public function test_it_call_processor_if_its_sleepy()
    {
        $message = new Message('body', [], 1);

        $provider = $this->prophesize(MessageProviderInterface::class);
        $provider->get()->shouldBeCalledTimes(2)->willReturn($message, null);
        $provider->getQueueName()->shouldBeCalledTimes(1)->willReturn('');

        $processor = $this->prophesize(SleepyInterface::class);
        $processor->sleep(Argument::type('array'))->shouldBeCalledTimes(1)->willReturn(false);
        $processor->process($message, Argument::type('array'))->shouldBeCalledTimes(1)->willReturn(true);

        $consumer = new Consumer($provider->reveal(), $processor->reveal());
        $consumer->consume();
    }
}
