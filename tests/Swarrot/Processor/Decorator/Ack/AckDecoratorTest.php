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
        $messageProvider = $this->prophesize('Swarrot\Broker\MessageProvider\MessageProviderInterface');

        $processor = new AckDecorator($messageProvider->reveal());
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $messageProvider = $this->prophesize('Swarrot\Broker\MessageProvider\MessageProviderInterface');
        $logger          = $this->prophesize('Psr\Log\LoggerInterface');

        $processor = new AckDecorator($messageProvider->reveal(), $logger->reveal());
    }

    public function test_it_should_ack_when_no_exception_is_thrown()
    {
        $decoratedProcessor = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $messageProvider    = $this->prophesize('Swarrot\Broker\MessageProvider\MessageProviderInterface');
        $logger             = $this->prophesize('Psr\Log\LoggerInterface');

        $message = new Message('body', array(), 1);

        $decoratedProcessor->process(Argument::exact($message), Argument::exact(array()))->willReturn(null);
        $messageProvider->ack(Argument::exact($message))->willReturn(null);

        $processor = new AckDecorator($messageProvider->reveal(), $logger->reveal());
        $this->assertNull($processor->decorate($decoratedProcessor->reveal(), $message, array()));
    }

    public function test_it_should_nack_when_an_exception_is_thrown()
    {
        $decoratedProcessor = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $messageProvider    = $this->prophesize('Swarrot\Broker\MessageProvider\MessageProviderInterface');
        $logger             = $this->prophesize('Psr\Log\LoggerInterface');

        $message = new Message('body', array(), 1);

        $decoratedProcessor->process(Argument::exact($message), Argument::exact(array()))->willThrow('\BadMethodCallException');
        $messageProvider->nack(Argument::exact($message), Argument::exact(false))->willReturn(null);

        $processor = new AckDecorator($messageProvider->reveal(), $logger->reveal());

        $this->setExpectedException('\BadMethodCallException');
        $this->assertNull($processor->decorate($decoratedProcessor->reveal(), $message, array()));
    }

    public function test_it_should_nack_and_requeue_when_an_exception_is_thrown_and_conf_updated()
    {
        $decoratedProcessor = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $messageProvider    = $this->prophesize('Swarrot\Broker\MessageProvider\MessageProviderInterface');
        $logger             = $this->prophesize('Psr\Log\LoggerInterface');

        $message = new Message('body', array(), 1);

        $decoratedProcessor->process(
            Argument::exact($message),
            Argument::exact(array('requeue_on_error' => true))
        )->willThrow('\BadMethodCallException');
        $messageProvider->nack(Argument::exact($message), Argument::exact(true))->willReturn(null);

        $processor = new AckDecorator($messageProvider->reveal(), $logger->reveal());

        $this->setExpectedException('\BadMethodCallException');
        $this->assertNull($processor->decorate($decoratedProcessor->reveal(), $message, array('requeue_on_error' => true)));
    }

    public function test_it_should_return_a_valid_array_of_option()
    {
        $messageProvider = $this->prophesize('Swarrot\Broker\MessageProvider\MessageProviderInterface');

        $processor = new AckDecorator($messageProvider->reveal());

        $optionsResolver = new OptionsResolver();
        $processor->setDefaultOptions($optionsResolver);

        $config = $optionsResolver->resolve(array(
            'requeue_on_error' => false
        ));

        $this->assertEquals(array(
            'requeue_on_error' => false
        ), $config);
    }
}
