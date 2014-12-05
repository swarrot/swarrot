<?php

namespace Swarrot\Processor\MaxMessages;

use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTestCase;
use Swarrot\Broker\Message;

class MaxMessagesProcessorTest extends ProphecyTestCase
{
    public function test_it_is_initializable_without_a_logger()
    {
        $processor = $this->prophesize('Swarrot\Processor\ProcessorInterface');

        $processor = new MaxMessagesProcessor($processor->reveal());
        $this->assertInstanceOf('Swarrot\Processor\MaxMessages\MaxMessagesProcessor', $processor);
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $processor = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $logger    = $this->prophesize('Psr\Log\LoggerInterface');

        $processor = new MaxMessagesProcessor($processor->reveal(), $logger->reveal());
        $this->assertInstanceOf('Swarrot\Processor\MaxMessages\MaxMessagesProcessor', $processor);
    }

    public function test_count_default_messages_processed()
    {
        $maxMessages = 2;
        $processor = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $processor->process(
            Argument::type('Swarrot\Broker\Message'),
            Argument::exact(array(
                'max_messages' => $maxMessages,
            ))
        )
        ->shouldBeCalledTimes(2);

        $logger = $this->prophesize('Psr\Log\LoggerInterface');
        $logger->info(
            Argument::exact(sprintf('[MaxMessages] Max messages have been reached (%d)', $maxMessages)),
            Argument::exact(array('swarrot_processor' => 'max_messages'))
        )
        ->shouldBeCalledTimes(1);

        $message = new Message('body', array(), 1);
        $processor = new MaxMessagesProcessor(
            $processor->reveal(),
            $logger->reveal()
        );

        // Process
        $this->assertNull($processor->process($message, array('max_messages' => $maxMessages)));

        // Too much messages processed, return false
        $this->assertFalse($processor->process($message, array('max_messages' => $maxMessages)));
    }
}
