<?php

namespace Swarrot\Processor\Decorator\Retry;

use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTestCase;
use Swarrot\Broker\Message;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RetryDecoratorTest extends ProphecyTestCase
{
    public function test_it_is_initializable_without_a_logger()
    {
        $messagePublisher = $this->prophesize('Swarrot\Broker\MessagePublisher\MessagePublisherInterface');

        $processor = new RetryDecorator($messagePublisher->reveal());
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $messagePublisher = $this->prophesize('Swarrot\Broker\MessagePublisher\MessagePublisherInterface');
        $logger           = $this->prophesize('Psr\Log\LoggerInterface');

        $processor = new RetryDecorator($messagePublisher->reveal(), $logger->reveal());
    }

    public function test_it_should_return_result_when_all_is_right()
    {
        $decoratedProcessor = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $messagePublisher   = $this->prophesize('Swarrot\Broker\MessagePublisher\MessagePublisherInterface');
        $logger             = $this->prophesize('Psr\Log\LoggerInterface');

        $message = new Message('body', array(), 1);

        $decoratedProcessor->process(Argument::exact($message), Argument::exact(array()))->willReturn(null);
        $messagePublisher
            ->publish(Argument::exact($message))
            ->shouldNotBeCalled(null)
        ;

        $processor = new RetryDecorator($messagePublisher->reveal(), $logger->reveal());
        $this->assertNull($processor->decorate($decoratedProcessor->reveal(), $message, array()));
    }

    public function test_it_should_republished_message_when_an_exception_occurred()
    {
        $decoratedProcessor = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $messagePublisher   = $this->prophesize('Swarrot\Broker\MessagePublisher\MessagePublisherInterface');
        $logger             = $this->prophesize('Psr\Log\LoggerInterface');

        $message = new Message('body', array(), 1);
        $options = array(
            'retry_attempts' => 3,
            'retry_key_pattern' => 'key_%attempt%',
        );

        $decoratedProcessor
            ->process(
                Argument::exact($message),
                Argument::exact($options)
            )->willThrow('\BadMethodCallException')
            ->shouldBeCalledTimes(1)
        ;
        $messagePublisher
            ->publish(
                Argument::type('Swarrot\Broker\Message'),
                Argument::exact('key_1')
            )
            ->willReturn(null)
            ->shouldBeCalledTimes(1)
        ;

        $processor = new RetryDecorator($messagePublisher->reveal(), $logger->reveal());

        $this->assertNull(
            $processor->decorate($decoratedProcessor->reveal(), $message, $options)
        );
    }

    public function test_it_should_republished_message_with_incremented_attempts()
    {
        $decoratedProcessor = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $messagePublisher   = $this->prophesize('Swarrot\Broker\MessagePublisher\MessagePublisherInterface');
        $logger             = $this->prophesize('Psr\Log\LoggerInterface');

        $message = new Message('body', array('headers' => array('swarrot_retry_attempts' => 1)), 1);

        $options = array(
            'retry_attempts' => 3,
            'retry_key_pattern' => 'key_%attempt%',
        );

        $decoratedProcessor
            ->process(
                Argument::exact($message),
                Argument::exact($options)
            )->willThrow('\BadMethodCallException')
            ->shouldBeCalledTimes(1)
        ;
        $messagePublisher
            ->publish(
                Argument::that(function(Message $message) {
                    $properties = $message->getProperties();

                    return 2 === $properties['headers']['swarrot_retry_attempts'] && 'body' === $message->getBody();
                }),

                Argument::exact('key_2')
            )
            ->willReturn(null)
            ->shouldBeCalledTimes(1)
        ;

        $processor = new RetryDecorator($messagePublisher->reveal(), $logger->reveal());

        $this->assertNull(
            $processor->decorate($decoratedProcessor->reveal(), $message, $options)
        );
    }

    public function test_it_should_throw_exception_if_max_attempts_is_reached()
    {
        $decoratedProcessor = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $messagePublisher   = $this->prophesize('Swarrot\Broker\MessagePublisher\MessagePublisherInterface');
        $logger             = $this->prophesize('Psr\Log\LoggerInterface');

        $message = new Message('body', array('headers' => array('swarrot_retry_attempts' => 3)), 1);
        $options = array(
            'retry_attempts' => 3,
            'retry_key_pattern' => 'key_%attempt%',
        );

        $decoratedProcessor
            ->process(
                Argument::exact($message),
                Argument::exact($options)
            )->willThrow('\BadMethodCallException')
            ->shouldBeCalledTimes(1)
        ;
        $messagePublisher
            ->publish(
                Argument::type('Swarrot\Broker\Message'),
                Argument::exact('key_1')
            )
            ->shouldNotBeCalled()
        ;

        $this->setExpectedException('\BadMethodCallException');
        $processor = new RetryDecorator($messagePublisher->reveal(), $logger->reveal());

        $processor->decorate($decoratedProcessor->reveal(), $message, $options);
    }

    public function test_it_should_return_a_valid_array_of_option()
    {
        $messagePublisher = $this->prophesize('Swarrot\Broker\MessagePublisher\MessagePublisherInterface');

        $processor = new RetryDecorator($messagePublisher->reveal());

        $optionsResolver = new OptionsResolver();
        $processor->setDefaultOptions($optionsResolver);

        $config = $optionsResolver->resolve(array(
            'retry_key_pattern' => 'key_%attempt%'
        ));

        $this->assertEquals(array(
            'retry_key_pattern' => 'key_%attempt%',
            'retry_attempts'    => 3
        ), $config);
    }
}
