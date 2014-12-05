<?php

namespace Swarrot\Processor\MaxExecutionTime;

use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTestCase;
use Swarrot\Broker\Message;

class MaxExecutionTimeProcessorTest extends ProphecyTestCase
{
    public function test_it_is_initializable_without_a_logger()
    {
        $processor = $this->prophesize('Swarrot\Processor\ProcessorInterface');

        $processor = new MaxExecutionTimeProcessor($processor->reveal());
        $this->assertInstanceOf('Swarrot\Processor\MaxExecutionTime\MaxExecutionTimeProcessor', $processor);
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $processor = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $logger    = $this->prophesize('Psr\Log\LoggerInterface');

        $processor = new MaxExecutionTimeProcessor($processor->reveal(), $logger->reveal());
        $this->assertInstanceOf('Swarrot\Processor\MaxExecutionTime\MaxExecutionTimeProcessor', $processor);
    }

    public function test_count_default_messages_processed()
    {
        $maxExecutionTime = 1;
        $processor = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $processor->process(
            Argument::type('Swarrot\Broker\Message'),
            Argument::exact(array(
                'max_execution_time' => $maxExecutionTime,
            ))
        );

        $logger    = $this->prophesize('Psr\Log\LoggerInterface');
        $logger->info(
            Argument::exact(sprintf('[MaxExecutionTime] Max execution time have been reached (%d)', $maxExecutionTime)),
            Argument::exact(array('swarrot_processor' => 'max_execution_time'))
        )
        ->shouldBeCalledTimes(1);

        $message = new Message('body', array(), 1);
        $processor = new MaxExecutionTimeProcessor(
            $processor->reveal(),
            $logger->reveal()
        );

        // Should be called by the Consumer
        $processor->initialize(array());

        $startTime = microtime(true);
        while (true) {
            if (false === $processor->process($message, array('max_execution_time' => $maxExecutionTime))) {
                break;
            }
        }

        $this->assertTrue(microtime(true) - $startTime > $maxExecutionTime);
    }
}
