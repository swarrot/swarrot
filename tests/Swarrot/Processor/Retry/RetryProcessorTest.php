<?php

namespace Swarrot\Processor\Retry;

use Prophecy\Argument;
use Swarrot\Broker\Message;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RetryProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function test_it_is_initializable_without_a_logger()
    {
        $processor        = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $messagePublisher = $this->prophesize('Swarrot\Broker\MessagePublisher\MessagePublisherInterface');

        $processor = new RetryProcessor($processor->reveal(), $messagePublisher->reveal());
        $this->assertInstanceOf('Swarrot\Processor\Retry\RetryProcessor', $processor);
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $processor        = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $messagePublisher = $this->prophesize('Swarrot\Broker\MessagePublisher\MessagePublisherInterface');
        $logger           = $this->prophesize('Psr\Log\LoggerInterface');

        $processor = new RetryProcessor($processor->reveal(), $messagePublisher->reveal(), $logger->reveal());
        $this->assertInstanceOf('Swarrot\Processor\Retry\RetryProcessor', $processor);
    }

    public function test_it_should_return_result_when_all_is_right()
    {
        $processor        = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $messagePublisher = $this->prophesize('Swarrot\Broker\MessagePublisher\MessagePublisherInterface');
        $logger           = $this->prophesize('Psr\Log\LoggerInterface');

        $message = new Message('body', array(), 1);

        $processor->process(Argument::exact($message), Argument::exact(array()))->willReturn(null);
        $messagePublisher
            ->publish(Argument::exact($message))
            ->shouldNotBeCalled(null)
        ;

        $processor = new RetryProcessor($processor->reveal(), $messagePublisher->reveal(), $logger->reveal());
        $this->assertNull($processor->process($message, array()));
    }

    public function test_it_should_republished_message_when_an_exception_occurred()
    {
        $processor        = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $messagePublisher = $this->prophesize('Swarrot\Broker\MessagePublisher\MessagePublisherInterface');
        $logger           = $this->prophesize('Psr\Log\LoggerInterface');

        $message = new Message('body', array(), 1);
        $options = array(
            'retry_attempts' => 3,
            'retry_key_pattern' => 'key_%attempt%',
        );

        $processor
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

        $processor = new RetryProcessor($processor->reveal(), $messagePublisher->reveal(), $logger->reveal());

        $this->assertNull(
            $processor->process($message, $options)
        );
    }

    public function test_it_should_republished_message_with_incremented_attempts()
    {
        $processor        = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $messagePublisher = $this->prophesize('Swarrot\Broker\MessagePublisher\MessagePublisherInterface');
        $logger           = $this->prophesize('Psr\Log\LoggerInterface');

        $message = new Message('body', array('headers' => array('swarrot_retry_attempts' => 1)), 1);

        $options = array(
            'retry_attempts' => 3,
            'retry_key_pattern' => 'key_%attempt%',
        );

        $processor
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

        $processor = new RetryProcessor($processor->reveal(), $messagePublisher->reveal(), $logger->reveal());

        $this->assertNull(
            $processor->process($message, $options)
        );
    }

    public function test_it_should_throw_exception_if_max_attempts_is_reached()
    {
        $processor        = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $messagePublisher = $this->prophesize('Swarrot\Broker\MessagePublisher\MessagePublisherInterface');
        $logger           = $this->prophesize('Psr\Log\LoggerInterface');

        $message = new Message('body', array('headers' => array('swarrot_retry_attempts' => 3)), 1);
        $options = array(
            'retry_attempts' => 3,
            'retry_key_pattern' => 'key_%attempt%',
        );

        $processor
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
        $processor = new RetryProcessor($processor->reveal(), $messagePublisher->reveal(), $logger->reveal());

        $processor->process($message, $options);
    }

    public function test_it_should_return_a_valid_array_of_option()
    {
        $processor        = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $messagePublisher = $this->prophesize('Swarrot\Broker\MessagePublisher\MessagePublisherInterface');

        $processor = new RetryProcessor($processor->reveal(), $messagePublisher->reveal());

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

    public function test_it_should_keep_original_message_properties()
    {
        $processor        = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $messagePublisher = $this->prophesize('Swarrot\Broker\MessagePublisher\MessagePublisherInterface');
        $logger           = $this->prophesize('Psr\Log\LoggerInterface');

        $message = new Message('body', array('delivery_mode' => 2, 'app_id' => 'applicationId', 'headers' => array('swarrot_retry_attempts' => 1)), 1);

        $options = array(
            'retry_attempts' => 3,
            'retry_key_pattern' => 'key_%attempt%',
        );

        $processor
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

                    return 2 === $properties['delivery_mode'] && 'applicationId' === $properties['app_id'];
                }),

                Argument::exact('key_2')
            )
            ->willReturn(null)
            ->shouldBeCalledTimes(1)
        ;

        $processor = new RetryProcessor($processor->reveal(), $messagePublisher->reveal(), $logger->reveal());

        $this->assertNull(
            $processor->process($message, $options)
        );
    }
    
    public function test_it_should_keep_original_message_headers()
    {
        $processor        = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $messagePublisher = $this->prophesize('Swarrot\Broker\MessagePublisher\MessagePublisherInterface');
        $logger           = $this->prophesize('Psr\Log\LoggerInterface');
    
        $message = new Message('body', array('headers' => array(
            'string' => 'foo',
            'integer' => 42,
        )), 1);
    
        $options = array(
            'retry_attempts' => 3,
            'retry_key_pattern' => 'key_%attempt%',
        );
    
        $processor
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
    
                    return 1 === $properties['headers']['swarrot_retry_attempts'] && 'foo' === $properties['headers']['string'] && 42 === $properties['headers']['integer'];
                }),
                Argument::exact('key_1')
            )
            ->willReturn(null)
            ->shouldBeCalledTimes(1)
        ;
    
        $processor = new RetryProcessor($processor->reveal(), $messagePublisher->reveal(), $logger->reveal());
    
        $this->assertNull(
            $processor->process($message, $options)
        );
    }
}
