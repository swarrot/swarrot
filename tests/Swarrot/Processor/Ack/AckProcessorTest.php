<?php

namespace Swarrot\Processor\Ack;

use Prophecy\Argument;
use Swarrot\Broker\Message;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AckProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function test_it_is_initializable_without_a_logger()
    {
        $processor       = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $messageProvider = $this->prophesize('Swarrot\Broker\MessageProvider\MessageProviderInterface');

        $processor = new AckProcessor($processor->reveal(), $messageProvider->reveal());
        $this->assertInstanceOf('Swarrot\Processor\Ack\AckProcessor', $processor);
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $processor       = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $messageProvider = $this->prophesize('Swarrot\Broker\MessageProvider\MessageProviderInterface');
        $logger          = $this->prophesize('Psr\Log\LoggerInterface');

        $processor = new AckProcessor($processor->reveal(), $messageProvider->reveal(), $logger->reveal());
        $this->assertInstanceOf('Swarrot\Processor\Ack\AckProcessor', $processor);
    }

    public function test_it_should_ack_when_no_exception_is_thrown()
    {
        $processor       = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $messageProvider = $this->prophesize('Swarrot\Broker\MessageProvider\MessageProviderInterface');
        $logger          = $this->prophesize('Psr\Log\LoggerInterface');

        $message = new Message('body', array(), 1);

        $processor->process(Argument::exact($message), Argument::exact(array()))->willReturn(null);
        $messageProvider->ack(Argument::exact($message))->willReturn(null);

        $processor = new AckProcessor($processor->reveal(), $messageProvider->reveal(), $logger->reveal());
        $this->assertNull($processor->process($message, array()));
    }

    public function test_it_should_nack_when_an_exception_is_thrown()
    {
        $processor       = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $messageProvider = $this->prophesize('Swarrot\Broker\MessageProvider\MessageProviderInterface');
        $logger          = $this->prophesize('Psr\Log\LoggerInterface');

        $message = new Message('body', array(), 1);

        $processor->process(Argument::exact($message), Argument::exact(array()))->willThrow('\BadMethodCallException');
        $messageProvider->nack(Argument::exact($message), Argument::exact(false))->willReturn(null);

        $processor = new AckProcessor($processor->reveal(), $messageProvider->reveal(), $logger->reveal());

        $this->setExpectedException('\BadMethodCallException');
        $this->assertNull($processor->process($message, array()));
    }

    public function test_it_should_nack_and_requeue_when_an_exception_is_thrown_and_conf_updated()
    {
        $processor       = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $messageProvider = $this->prophesize('Swarrot\Broker\MessageProvider\MessageProviderInterface');
        $logger          = $this->prophesize('Psr\Log\LoggerInterface');

        $message = new Message('body', array(), 1);

        $processor->process(
            Argument::exact($message),
            Argument::exact(array('requeue_on_error' => true))
        )->willThrow('\BadMethodCallException');
        $messageProvider->nack(Argument::exact($message), Argument::exact(true))->willReturn(null);

        $processor = new AckProcessor($processor->reveal(), $messageProvider->reveal(), $logger->reveal());

        $this->setExpectedException('\BadMethodCallException');
        $this->assertNull($processor->process($message, array('requeue_on_error' => true)));
    }

    public function test_it_should_return_a_valid_array_of_option()
    {
        $processor       = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $messageProvider = $this->prophesize('Swarrot\Broker\MessageProvider\MessageProviderInterface');

        $processor = new AckProcessor($processor->reveal(), $messageProvider->reveal());

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
