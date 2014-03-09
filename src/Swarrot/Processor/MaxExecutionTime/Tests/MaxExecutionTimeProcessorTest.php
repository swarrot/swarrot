<?php

namespace Swarrot\Processor\MaxExecutionTime;

use Prophecy\Argument;
use Swarrot\Broker\Message;

class MaxExecutionTimeProcessorTest extends \PHPUnit_Framework_TestCase
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

        $processor = new MaxExecutionTimeProcessor($processor->reveal());
        $this->assertInstanceOf('Swarrot\Processor\MaxExecutionTime\MaxExecutionTimeProcessor', $processor);
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $processor = $this->prophet->prophesize('Swarrot\Processor\ProcessorInterface');
        $logger    = $this->prophet->prophesize('Psr\Log\LoggerInterface');

        $processor = new MaxExecutionTimeProcessor($processor->reveal(), $logger->reveal());
        $this->assertInstanceOf('Swarrot\Processor\MaxExecutionTime\MaxExecutionTimeProcessor', $processor);
    }

    public function test_count_default_messages_processed()
    {
        $maxExecutionTime = 1;
        $processor = $this->prophet->prophesize('Swarrot\Processor\ProcessorInterface');
        $processor->process(
            Argument::type('Swarrot\Broker\Message'),
            Argument::exact(array(
                'max_execution_time' => $maxExecutionTime,
            ))
        );

        $logger    = $this->prophet->prophesize('Psr\Log\LoggerInterface');
        $logger->info(
            Argument::exact(sprintf('[MaxExecutionTime] Max execution time have been reached (%d)', $maxExecutionTime))
        )
        ->shouldBeCalledTimes(1);

        $message = new Message(1, 'body');
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
