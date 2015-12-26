<?php

namespace Swarrot\Processor\RPC;

use Prophecy\Argument;
use Swarrot\Broker\Message;

class RpcClientProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function test_it_is_initializable_without_a_logger()
    {
        $processor = new RpcClientProcessor();
        $this->assertInstanceOf('Swarrot\\Processor\\RPC\\RpcClientProcessor', $processor);
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $logger = $this->prophesize('Psr\\Log\\LoggerInterface');

        $processor = new RpcClientProcessor(null, $logger->reveal());
        $this->assertInstanceOf('Swarrot\\Processor\\RPC\\RpcClientProcessor', $processor);
    }

    public function test_it_is_initializable_with_a_processor()
    {
        $processor = $this->prophesize('Swarrot\\Processor\\ProcessorInterface');

        $processor = new RpcClientProcessor($processor->reveal());
        $this->assertInstanceOf('Swarrot\\Processor\\RPC\\RpcClientProcessor', $processor);
    }

    public function test_it_is_initializable_with_a_processor_and_a_logger()
    {
        $processor = $this->prophesize('Swarrot\\Processor\\ProcessorInterface');
        $logger = $this->prophesize('Psr\\Log\\LoggerInterface');

        $processor = new RpcClientProcessor($processor->reveal(), $logger->reveal());
        $this->assertInstanceOf('Swarrot\\Processor\\RPC\\RpcClientProcessor', $processor);
    }

    public function test_it_should_sleep_if_no_correlation_id_set()
    {
        $processor = new RpcClientProcessor;
        $this->assertNull($processor->process(new Message, []));
    }

    public function test_it_should_sleep_if_invalid_correlation_id()
    {
        $processor = new RpcClientProcessor;
        $message = new Message(null, ['correlation_id' => 1]);

        $this->assertNull($processor->process($message, ['rpc_client_correlation_id' => 0]));
        $this->assertTrue($processor->sleep([]));
    }

    public function test_it_should_stop_if_correct_correlation_id()
    {
        $processor = new RpcClientProcessor;
        $message = new Message(null, ['correlation_id' => 1]);

        $this->assertNull($processor->process($message, ['rpc_client_correlation_id' => 1]));
        $this->assertFalse($processor->sleep([]));
    }

    public function test_it_should_let_the_nested_processor_act_and_stop_if_correct_correlation_id()
    {
        $message = new Message(null, ['correlation_id' => 1]);

        $processor = $this->prophesize('Swarrot\\Processor\\ProcessorInterface');
        $processor->process($message, ['rpc_client_correlation_id' => 1])->willReturn(true)->shouldBeCalled();
        $processor = new RpcClientProcessor($processor->reveal());

        $this->assertTrue($processor->process($message, ['rpc_client_correlation_id' => 1]));
        $this->assertFalse($processor->sleep([]));
    }
}

