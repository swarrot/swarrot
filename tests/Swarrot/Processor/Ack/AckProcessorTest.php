<?php

namespace Swarrot\Tests\Processor\Ack;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Swarrot\Broker\Message;
use Swarrot\Broker\MessageProvider\MessageProviderInterface;
use Swarrot\Processor\Ack\AckProcessor;
use Swarrot\Processor\ProcessorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AckProcessorTest extends TestCase
{
    public function test_it_is_initializable_without_a_logger()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $messageProvider = $this->prophesize(MessageProviderInterface::class);

        $processor = new AckProcessor($processor->reveal(), $messageProvider->reveal());
        $this->assertInstanceOf(AckProcessor::class, $processor);
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $messageProvider = $this->prophesize(MessageProviderInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $processor = new AckProcessor($processor->reveal(), $messageProvider->reveal(), $logger->reveal());
        $this->assertInstanceOf(AckProcessor::class, $processor);
    }

    public function test_it_should_ack_when_no_exception_is_thrown()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $messageProvider = $this->prophesize(MessageProviderInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $message = new Message('body', [], 1);

        $processor->process(Argument::exact($message), Argument::exact([]))->willReturn(null);
        $messageProvider->ack(Argument::exact($message))->willReturn(null);

        $processor = new AckProcessor($processor->reveal(), $messageProvider->reveal(), $logger->reveal());
        $this->assertNull($processor->process($message, []));
    }

    public function test_it_should_nack_when_an_exception_is_thrown()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $messageProvider = $this->prophesize(MessageProviderInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $message = new Message('body', [], 1);

        $processor->process(Argument::exact($message), Argument::exact([]))->willThrow('\BadMethodCallException');
        $messageProvider->nack(Argument::exact($message), Argument::exact(false))->willReturn(null);

        $processor = new AckProcessor($processor->reveal(), $messageProvider->reveal(), $logger->reveal());

        $this->expectException('\BadMethodCallException');
        $this->assertNull($processor->process($message, []));
    }

    public function test_it_should_nack_and_requeue_when_an_exception_is_thrown_and_conf_updated()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $messageProvider = $this->prophesize(MessageProviderInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $message = new Message('body', [], 1);

        $processor->process(
            Argument::exact($message),
            Argument::exact(['requeue_on_error' => true])
        )->willThrow('\BadMethodCallException');
        $messageProvider->nack(Argument::exact($message), Argument::exact(true))->willReturn(null);

        $processor = new AckProcessor($processor->reveal(), $messageProvider->reveal(), $logger->reveal());

        $this->expectException('\BadMethodCallException');
        $this->assertNull($processor->process($message, ['requeue_on_error' => true]));
    }

    public function test_it_should_return_a_valid_array_of_option()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $messageProvider = $this->prophesize(MessageProviderInterface::class);

        $processor = new AckProcessor($processor->reveal(), $messageProvider->reveal());

        $optionsResolver = new OptionsResolver();
        $processor->setDefaultOptions($optionsResolver);

        $config = $optionsResolver->resolve([
            'requeue_on_error' => false,
        ]);

        $this->assertEquals([
            'requeue_on_error' => false,
        ], $config);
    }
}
