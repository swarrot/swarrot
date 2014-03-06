<?php

namespace Swarrot\Processor\AckProcessor;

use Swarrot\Processor\AckProcessor\AckProcessor;
use Prophecy\Argument;
use Swarrot\Broker\Message;

class AckProcessorTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->prophet = new \Prophecy\Prophet;
    }

    protected function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function test_it_is_initializable_without_a_logger()
    {
        $processor       = $this->prophet->prophesize('Swarrot\Processor\ProcessorInterface');
        $messageProvider = $this->prophet->prophesize('Swarrot\Broker\MessageProviderInterface');

        $processor = new AckProcessor($processor->reveal(), $messageProvider->reveal());
        $this->assertInstanceOf('Swarrot\Processor\AckProcessor\AckProcessor', $processor);
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $processor = $this->prophet->prophesize('Swarrot\Processor\ProcessorInterface');
        $messageProvider    = $this->prophet->prophesize('Swarrot\Broker\MessageProviderInterface');
        $logger             = $this->prophet->prophesize('Psr\Log\LoggerInterface');

        $processor = new AckProcessor($processor->reveal(), $messageProvider->reveal(), $logger->reveal());
        $this->assertInstanceOf('Swarrot\Processor\AckProcessor\AckProcessor', $processor);
    }

    public function test_it_should_ack_when_no_exception_is_thrown()
    {
        $processor = $this->prophet->prophesize('Swarrot\Processor\ProcessorInterface');
        $messageProvider    = $this->prophet->prophesize('Swarrot\Broker\MessageProviderInterface');
        $logger             = $this->prophet->prophesize('Psr\Log\LoggerInterface');

        $message = new Message(1, 'body');

        $processor->__invoke(Argument::exact($message), Argument::exact(array()))->willReturn(null);
        $messageProvider->ack(Argument::exact($message))->willReturn(null);

        $processor = new AckProcessor($processor->reveal(), $messageProvider->reveal(), $logger->reveal());
        $this->assertNull($processor->__invoke($message, array()));
    }

    public function test_it_should_nack_when_an_exception_is_thrown()
    {
        $processor = $this->prophet->prophesize('Swarrot\Processor\ProcessorInterface');
        $messageProvider    = $this->prophet->prophesize('Swarrot\Broker\MessageProviderInterface');
        $logger             = $this->prophet->prophesize('Psr\Log\LoggerInterface');

        $message = new Message(1, 'body');

        $processor->__invoke(Argument::exact($message), Argument::exact(array()))->willThrow('\BadMethodCallException');
        $messageProvider->nack(Argument::exact($message), Argument::exact(false))->willReturn(null);

        $processor = new AckProcessor($processor->reveal(), $messageProvider->reveal(), $logger->reveal());

        $this->setExpectedException('\BadMethodCallException');
        $this->assertNull($processor->__invoke($message, array()));
    }

    public function test_it_should_nack_and_requeue_when_an_exception_is_thrown_and_conf_updated()
    {
        $processor = $this->prophet->prophesize('Swarrot\Processor\ProcessorInterface');
        $messageProvider    = $this->prophet->prophesize('Swarrot\Broker\MessageProviderInterface');
        $logger             = $this->prophet->prophesize('Psr\Log\LoggerInterface');

        $message = new Message(1, 'body');

        $processor->__invoke(Argument::exact($message), Argument::exact(array('requeue_on_error' => true)))->willThrow('\BadMethodCallException');
        $messageProvider->nack(Argument::exact($message), Argument::exact(true))->willReturn(null);

        $processor = new AckProcessor($processor->reveal(), $messageProvider->reveal(), $logger->reveal());

        $this->setExpectedException('\BadMethodCallException');
        $this->assertNull($processor->__invoke($message, array('requeue_on_error' => true)));
    }
}
