<?php

namespace Swarrot\Processor\MaxMessages;

use Swarrot\Processor\MaxMessages\MaxMessagesProcessor;
use Prophecy\Argument;
use Swarrot\Broker\Message;

class MaxMessagesProcessorTest extends \PHPUnit_Framework_TestCase
{
    protected $prophet;

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
        $processor = $this->prophet->prophesize('Swarrot\Processor\ProcessorInterface');

        $processor = new MaxMessagesProcessor($processor->reveal());
        $this->assertInstanceOf('Swarrot\Processor\MaxMessages\MaxMessagesProcessor', $processor);
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $processor = $this->prophet->prophesize('Swarrot\Processor\ProcessorInterface');
        $logger    = $this->prophet->prophesize('Psr\Log\LoggerInterface');

        $processor = new MaxMessagesProcessor($processor->reveal(), $logger->reveal());
        $this->assertInstanceOf('Swarrot\Processor\MaxMessages\MaxMessagesProcessor', $processor);
    }

    public function test_count_default_messages_processed()
    {
        $maxMessages = 2;
        $processor = $this->prophet->prophesize('Swarrot\Processor\ProcessorInterface');
        $processor->process(
            Argument::type('Swarrot\Broker\Message'),
            Argument::exact(array(
                'max_messages' => $maxMessages,
            ))
        )
        ->shouldBeCalledTimes(2);

        $logger    = $this->prophet->prophesize('Psr\Log\LoggerInterface');
        $logger->debug(
            Argument::exact(sprintf('Max messages have been reached (%d)', $maxMessages))
        )
        ->shouldBeCalledTimes(1);

        $message = new Message(1, 'body');
        $processor = new MaxMessagesProcessor(
            $processor->reveal(),
            $logger->reveal()
        );

        // Process
        $this->assertNull($processor->process($message, array('max_messages' => $maxMessages)));
        // Process
        $this->assertNull($processor->process($message, array('max_messages' => $maxMessages)));

        // Too much messages processed, return false
        $this->assertFalse($processor->process($message, array('max_messages' => $maxMessages)));
    }
}
