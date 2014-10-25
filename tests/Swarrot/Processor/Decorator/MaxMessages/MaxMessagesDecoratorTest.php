<?php

namespace Swarrot\Processor\Decorator\MaxMessages;

use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTestCase;
use Swarrot\Broker\Message;

class MaxMessagesDecoratorTest extends ProphecyTestCase
{
    public function test_it_is_initializable_without_a_logger()
    {
        $processor = new MaxMessagesDecorator();
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $logger = $this->prophesize('Psr\Log\LoggerInterface');

        $processor = new MaxMessagesDecorator($logger->reveal());
    }

    public function test_count_default_messages_processed()
    {
        $maxMessages = 2;
        $decoratedProcessor = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $decoratedProcessor->process(
            Argument::type('Swarrot\Broker\Message'),
            Argument::exact(array(
                'max_messages' => $maxMessages,
            ))
        )
        ->shouldBeCalledTimes(2);

        $logger = $this->prophesize('Psr\Log\LoggerInterface');
        $logger->info(
            Argument::exact(sprintf('[MaxMessages] Max messages have been reached (%d)', $maxMessages))
        )
        ->shouldBeCalledTimes(1);

        $message = new Message('body', array(), 1);
        $processor = new MaxMessagesDecorator($logger->reveal());

        // Process
        $this->assertNull($processor->decorate($decoratedProcessor->reveal(),$message, array('max_messages' => $maxMessages)));

        // Too much messages processed, return false
        $this->assertFalse($processor->decorate($decoratedProcessor->reveal(),$message, array('max_messages' => $maxMessages)));
    }
}
