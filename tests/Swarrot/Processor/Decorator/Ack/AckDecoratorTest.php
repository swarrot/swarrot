<?php

namespace Swarrot\Processor\Decorator\Ack;

use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTestCase;
use Swarrot\Broker\Message;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AckDecoratorTest extends ProphecyTestCase
{
    public function test_it_is_initializable_without_a_logger()
    {
        $processor = new AckDecorator();
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $logger          = $this->prophesize('Psr\Log\LoggerInterface');

        $processor = new AckDecorator($logger->reveal());
    }

    public function test_it_should_ack_when_no_exception_is_thrown()
    {
        $decoratedProcessor = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $messageProvider    = $this->prophesize('Swarrot\Broker\MessageProvider\MessageProviderInterface');
        $logger             = $this->prophesize('Psr\Log\LoggerInterface');

        $message = new Message('body', array(), 1);
        $options = ['message_provider' => $messageProvider->reveal()];

        $decoratedProcessor->process($message, $options)->willReturn(null);
        $messageProvider->ack($message)->willReturn(null);

        $processor = new AckDecorator($logger->reveal());
        $this->assertNull($processor->decorate($decoratedProcessor->reveal(), $message, $options));
    }

    public function test_it_should_nack_when_an_exception_is_thrown()
    {
        $decoratedProcessor = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $messageProvider    = $this->prophesize('Swarrot\Broker\MessageProvider\MessageProviderInterface');
        $logger             = $this->prophesize('Psr\Log\LoggerInterface');

        $message = new Message('body', array(), 1);
        $options = ['message_provider' => $messageProvider->reveal()];

        $decoratedProcessor->process($message, $options)->willThrow('\BadMethodCallException');
        $messageProvider->nack($message, false)->willReturn(null);

        $processor = new AckDecorator($logger->reveal());

        $this->setExpectedException('\BadMethodCallException');
        $this->assertNull($processor->decorate($decoratedProcessor->reveal(), $message, $options));
    }

    public function test_it_should_nack_and_requeue_when_an_exception_is_thrown_and_conf_updated()
    {
        $decoratedProcessor = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $messageProvider    = $this->prophesize('Swarrot\Broker\MessageProvider\MessageProviderInterface');
        $logger             = $this->prophesize('Psr\Log\LoggerInterface');

        $message = new Message('body', array(), 1);
        $options = [
            'requeue_on_error' => true,
            'message_provider' => $messageProvider->reveal(),
        ];

        $decoratedProcessor->process($message, $options)->willThrow('\BadMethodCallException');
        $messageProvider->nack($message, true)->willReturn(null);

        $processor = new AckDecorator($logger->reveal());



        $this->setExpectedException('\BadMethodCallException');
        $this->assertNull($processor->decorate($decoratedProcessor->reveal(), $message, $options));
    }

    public function test_it_should_configure_options_resolver()
    {
        $optionsResolver = $this->prophesize('Symfony\Component\OptionsResolver\OptionsResolver');

        $optionsResolver
            ->setDefaults(['requeue_on_error' => false])
            ->willReturn($optionsResolver)
            ->shouldBeCalled()
        ;
        $optionsResolver
            ->setAllowedTypes([
                'requeue_on_error' => 'bool',
                'message_provider' => 'Swarrot\Broker\MessageProvider\MessageProviderInterface'
            ])
            ->willReturn($optionsResolver)
            ->shouldBeCalled()
        ;
        $optionsResolver
            ->setRequired(['message_provider'])
            ->willReturn($optionsResolver)
            ->shouldBeCalled()
        ;

        $processor = new AckDecorator();
        $processor->setDefaultOptions($optionsResolver->reveal());
    }
}
