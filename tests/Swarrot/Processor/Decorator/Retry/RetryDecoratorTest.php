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
        $processor = new RetryDecorator();
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $logger = $this->prophesize('Psr\Log\LoggerInterface');

        $processor = new RetryDecorator($logger->reveal());
    }

    public function test_it_should_return_result_when_all_is_right()
    {
        $decoratedProcessor = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $messagePublisher   = $this->prophesize('Swarrot\Broker\MessagePublisher\MessagePublisherInterface');
        $logger             = $this->prophesize('Psr\Log\LoggerInterface');

        $message = new Message('body', array(), 1);
        $options = ['retry_publisher' => $messagePublisher->reveal()];

        $decoratedProcessor->process($message, $options)->willReturn(null);
        $messagePublisher
            ->publish($message)
            ->shouldNotBeCalled(null)
        ;

        $processor = new RetryDecorator($logger->reveal());
        $this->assertNull($processor->decorate($decoratedProcessor->reveal(), $message, $options));
    }

    public function test_it_should_republished_message_when_an_exception_occurred()
    {
        $decoratedProcessor = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $messagePublisher   = $this->prophesize('Swarrot\Broker\MessagePublisher\MessagePublisherInterface');
        $logger             = $this->prophesize('Psr\Log\LoggerInterface');

        $message = new Message(
            'body',
            [
                'content_type' => 'application/json',
                'headers' => [
                    'foo' => 'bar',
                ]
            ],
            1
        );
        $options = array(
            'retry_attempts' => 3,
            'retry_key_pattern' => 'key_%attempt%',
            'retry_publisher' => $messagePublisher->reveal(),
        );

        $decoratedProcessor
            ->process($message, $options)
            ->willThrow('\BadMethodCallException')
            ->shouldBeCalledTimes(1)
        ;
        $messagePublisher
            ->publish(
                Argument::allOf(
                    Argument::type('Swarrot\Broker\Message'),
                    Argument::which('getBody', $message->getBody()),
                    Argument::which(
                        'getProperties',
                        array_merge(
                            $message->getProperties(),
                            [
                                'headers' => [
                                    'swarrot_retry_attempts' => 1,
                                    'foo' => 'bar',
                                ]
                            ]
                        )
                    )
                ),
                'key_1'
            )
            ->willReturn(null)
            ->shouldBeCalledTimes(1)
        ;

        $processor = new RetryDecorator($logger->reveal());

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
            'retry_publisher' => $messagePublisher->reveal(),
        );

        $decoratedProcessor
            ->process($message, $options)
            ->willThrow('\BadMethodCallException')
            ->shouldBeCalledTimes(1)
        ;
        $messagePublisher
            ->publish(
                Argument::allOf(
                    Argument::type('Swarrot\Broker\Message'),
                    Argument::which('getBody', $message->getBody()),
                    Argument::which(
                        'getProperties',
                        array_merge(
                            $message->getProperties(),
                            ['headers' => ['swarrot_retry_attempts' => 2]]
                        )
                    )
                ),

                Argument::exact('key_2')
            )
            ->willReturn(null)
            ->shouldBeCalledTimes(1)
        ;

        $processor = new RetryDecorator($logger->reveal());

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
            'retry_publisher' => $messagePublisher->reveal(),
        );

        $decoratedProcessor
            ->process($message, $options)
            ->willThrow('\BadMethodCallException')
            ->shouldBeCalledTimes(1)
        ;
        $messagePublisher
            ->publish(
                Argument::type('Swarrot\Broker\Message'),
                'key_1'
            )
            ->shouldNotBeCalled()
        ;

        $this->setExpectedException('\BadMethodCallException');
        $processor = new RetryDecorator($logger->reveal());

        $processor->decorate($decoratedProcessor->reveal(), $message, $options);
    }

    public function test_it_should_return_a_valid_array_of_option()
    {
        $messagePublisher = $this->prophesize('Swarrot\Broker\MessagePublisher\MessagePublisherInterface');

        $processor = new RetryDecorator();

        $optionsResolver = new OptionsResolver();
        $processor->setDefaultOptions($optionsResolver);

        $config = $optionsResolver->resolve(array(
            'retry_key_pattern' => 'key_%attempt%',
            'retry_publisher'   => $messagePublisher->reveal(),
        ));

        $this->assertEquals(array(
            'retry_key_pattern' => 'key_%attempt%',
            'retry_attempts'    => 3,
            'retry_publisher'   => $messagePublisher->reveal(),
        ), $config);
    }
}
