<?php

namespace Swarrot\Tests\Processor\MaxMessages;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use Swarrot\Broker\Message;
use Swarrot\Processor\MaxMessages\MaxMessagesProcessor;
use Swarrot\Processor\ProcessorInterface;

class MaxMessagesProcessorTest extends TestCase
{
    use ProphecyTrait;

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
        $message = new Message('body', [], 1);

        $maxMessages = 2;
        $processor = $this->prophesize(ProcessorInterface::class);
        $processor->process($message, ['max_messages' => $maxMessages])->willReturn(true)->shouldBeCalledTimes(2);

        $logger = $this->prophesize(LoggerInterface::class);
        $logger->info('[MaxMessages] The maximum number of messages has been reached', [
            'max_messages' => 2,
            'swarrot_processor' => 'max_messages',
        ])->shouldBeCalledTimes(1);

        $processor = new MaxMessagesProcessor($processor->reveal(), $logger->reveal());

        $this->assertTrue($processor->process($message, ['max_messages' => $maxMessages]));
        $this->assertFalse($processor->process($message, ['max_messages' => $maxMessages]));
    }
}
