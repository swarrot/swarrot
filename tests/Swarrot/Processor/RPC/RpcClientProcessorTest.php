<?php

namespace Swarrot\Processor\RPC;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;

class RpcClientProcessorTest extends TestCase
{
    public function test_it_is_initializable_without_a_logger()
    {
        $processor = new RpcClientProcessor();
        $this->assertInstanceOf(RpcClientProcessor::class, $processor);
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $logger = $this->prophesize(LoggerInterface::class);

        $processor = new RpcClientProcessor(null, $logger->reveal());
        $this->assertInstanceOf(RpcClientProcessor::class, $processor);
    }

    public function test_it_is_initializable_with_a_processor()
    {
        $processor = $this->prophesize(ProcessorInterface::class);

        $processor = new RpcClientProcessor($processor->reveal());
        $this->assertInstanceOf(RpcClientProcessor::class, $processor);
    }

    public function test_it_is_initializable_with_a_processor_and_a_logger()
    {
        $processor = $this->prophesize(ProcessorInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $processor = new RpcClientProcessor($processor->reveal(), $logger->reveal());
        $this->assertInstanceOf(RpcClientProcessor::class, $processor);
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

        $processor = $this->prophesize(ProcessorInterface::class);
        $processor->process($message, ['rpc_client_correlation_id' => 1])->willReturn(true)->shouldBeCalled();
        $processor = new RpcClientProcessor($processor->reveal());

        $this->assertTrue($processor->process($message, ['rpc_client_correlation_id' => 1]));
        $this->assertFalse($processor->sleep([]));
    }
}

