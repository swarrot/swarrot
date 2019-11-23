<?php

namespace Swarrot\Tests\Processor\MaxMessages;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Swarrot\Broker\Message;
use Swarrot\Processor\MaxMessages\MaxMessagesProcessor;
use Swarrot\Processor\ProcessorInterface;

class MaxMessagesProcessorTest extends TestCase
{
    public function test_it_is_initializable_without_a_logger()
    {
        $processor = $this->prophesize(ProcessorInterface::class);

        $processor = new MaxMessagesProcessor($processor->reveal());
        $this->assertInstanceOf(MaxMessagesProcessor::class, $processor);
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $processor = new MaxMessagesProcessor($processor->reveal(), $logger->reveal());
        $this->assertInstanceOf(MaxMessagesProcessor::class, $processor);
    }

    public function test_count_default_messages_processed()
    {
        $maxMessages = 2;
        $processor = $this->prophesize(ProcessorInterface::class);
        $processor->process(
            Argument::type(Message::class),
            Argument::exact([
                'max_messages' => $maxMessages,
            ])
        )
        ->shouldBeCalledTimes(2);

        $logger = $this->prophesize(LoggerInterface::class);
        $logger->info(
            Argument::exact('[MaxMessages] The maximum number of messages has been reached'),
            Argument::exact(['max_messages' => 2, 'swarrot_processor' => 'max_messages'])
        )
        ->shouldBeCalledTimes(1);

        $message = new Message('body', [], 1);
        $processor = new MaxMessagesProcessor(
            $processor->reveal(),
            $logger->reveal()
        );

        // Process
        $this->assertNull($processor->process($message, ['max_messages' => $maxMessages]));

        // Too much messages processed, return false
        $this->assertFalse($processor->process($message, ['max_messages' => $maxMessages]));
    }
}
