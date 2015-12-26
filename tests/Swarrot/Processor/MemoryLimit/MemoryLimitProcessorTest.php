<?php

namespace Swarrot\Processor\MemoryLimit;

use Prophecy\Argument;
use Swarrot\Broker\Message;

class MemoryLimitProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function test_it_is_initializable_without_a_logger()
    {
        $processor = $this->prophesize('Swarrot\Processor\ProcessorInterface');

        $processor = new MemoryLimitProcessor($processor->reveal());
        $this->assertInstanceOf('Swarrot\Processor\MemoryLimit\MemoryLimitProcessor', $processor);
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $processor = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $logger    = $this->prophesize('Psr\Log\LoggerInterface');

        $processor = new MemoryLimitProcessor($processor->reveal(), $logger->reveal());
        $this->assertInstanceOf('Swarrot\Processor\MemoryLimit\MemoryLimitProcessor', $processor);
    }

    public function test_delegate_processing()
    {
        $processor = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $processor->process(
            Argument::type('Swarrot\Broker\Message'),
            Argument::exact(array(
                'memory_limit' => null,
            ))
        )
        ->shouldBeCalledTimes(1);

        $logger = $this->prophesize('Psr\Log\LoggerInterface');

        $message = new Message('body', array(), 1);
        $processor = new MemoryLimitProcessor(
            $processor->reveal(),
            $logger->reveal()
        );

        // Process
        $this->assertNull($processor->process($message, array('memory_limit' => null)));
    }
}
