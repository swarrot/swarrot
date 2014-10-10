<?php

namespace Swarrot\Processor\Decorator\MaxExecutionTime;

use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTestCase;
use Swarrot\Broker\Message;

class MaxExecutionTimeProcessorTest extends ProphecyTestCase
{
    public function test_it_is_initializable_without_a_logger()
    {
        $processor = new MaxExecutionTimeDecorator();
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $logger = $this->prophesize('Psr\Log\LoggerInterface');

        $processor = new MaxExecutionTimeDecorator($logger->reveal());
    }

    public function test_count_default_messages_processed()
    {
        $maxExecutionTime = 1;
        $decoratedProcessor = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $decoratedProcessor->process(
            Argument::type('Swarrot\Broker\Message'),
            Argument::exact(array(
                'max_execution_time' => $maxExecutionTime,
            ))
        );

        $logger = $this->prophesize('Psr\Log\LoggerInterface');
        $logger->info(
            Argument::exact(sprintf('[MaxExecutionTime] Max execution time have been reached (%d)', $maxExecutionTime))
        )
        ->shouldBeCalledTimes(1);

        $message = new Message('body', array(), 1);
        $processor = new MaxExecutionTimeDecorator($logger->reveal());

        // Should be called by the Consumer
        $processor->initialize(array());

        $startTime = microtime(true);
        while (true) {
            if (false === $processor->decorate($decoratedProcessor->reveal(), $message, array('max_execution_time' => $maxExecutionTime))) {
                break;
            }
        }

        $this->assertTrue(microtime(true) - $startTime > $maxExecutionTime);
    }
}
