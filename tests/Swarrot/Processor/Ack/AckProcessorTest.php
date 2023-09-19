<?php

namespace Swarrot\Tests\Processor\Ack;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use Swarrot\Broker\Message;
use Swarrot\Broker\MessageProvider\MessageProviderInterface;
use Swarrot\Processor\Ack\AckProcessor;
use Swarrot\Processor\ProcessorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AckProcessorTest extends TestCase
{
    use ProphecyTrait;

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
        $message = new Message('body', [], 1);

        $processor = $this->prophesize(ProcessorInterface::class);
        $processor->process($message, [])->shouldBeCalledTimes(1)->willReturn(true);

        $messageProvider = $this->prophesize(MessageProviderInterface::class);
        $messageProvider->ack($message)->shouldBeCalledTimes(1);

        $logger = $this->prophesize(LoggerInterface::class);

        $processor = new AckProcessor($processor->reveal(), $messageProvider->reveal(), $logger->reveal());
        $this->assertTrue($processor->process($message, []));
    }

    public function test_it_should_nack_when_an_exception_is_thrown()
    {
        $message = new Message('body', [], 1);

        $processor = $this->prophesize(ProcessorInterface::class);
        $processor->process($message, [])->willThrow(new \BadMethodCallException());

        $messageProvider = $this->prophesize(MessageProviderInterface::class);
        $messageProvider->nack($message, false)->shouldBeCalledTimes(1);

        $logger = $this->prophesize(LoggerInterface::class);

        $processor = new AckProcessor($processor->reveal(), $messageProvider->reveal(), $logger->reveal());

        $this->expectException('\BadMethodCallException');
        $processor->process($message, []);
    }

    public function test_it_should_nack_and_requeue_when_an_exception_is_thrown_and_conf_updated()
    {
        $message = new Message('body', [], 1);

        $processor = $this->prophesize(ProcessorInterface::class);
        $processor->process($message, ['requeue_on_error' => true])->shouldBeCalledTimes(1)->willThrow(new \BadMethodCallException());

        $messageProvider = $this->prophesize(MessageProviderInterface::class);
        $messageProvider->nack($message, true)->shouldBeCalledTimes(1);

        $logger = $this->prophesize(LoggerInterface::class);

        $processor = new AckProcessor($processor->reveal(), $messageProvider->reveal(), $logger->reveal());

        $this->expectException('\BadMethodCallException');
        $processor->process($message, ['requeue_on_error' => true]);
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
