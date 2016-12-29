<?php

namespace Swarrot\Processor\RPC;

use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Broker\MessagePublisher\MessagePublisherInterface;

class RpcServerProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectProphecy|ProcessorInterface
     */
    private $parentProcessor;

    /**
     * @var ObjectProphecy|MessagePublisherInterface
     */
    private $messagePublisher;

    /**
     * @var ObjectProphecy|LoggerInterface
     */
    private $logger;

    /**
     * @var RpcServerProcessor
     */
    private $processor;

    public function setUp()
    {
        $this->parentProcessor = $this->prophesize(ProcessorInterface::class);
        $this->messagePublisher = $this->prophesize(MessagePublisherInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->processor = new RpcServerProcessor(
            $this->parentProcessor->reveal(),
            $this->messagePublisher->reveal(),
            $this->logger->reveal()
        );
    }

    public function test_it_is_initializable_without_a_logger()
    {
        $processor = new RpcServerProcessor($this->parentProcessor->reveal(), $this->messagePublisher->reveal());
        $this->assertInstanceOf('Swarrot\\Processor\\RPC\\RpcServerProcessor', $processor);
    }

    public function test_it_is_initializable_with_a_logger()
    {
        $processor = new RpcServerProcessor(
            $this->parentProcessor->reveal(),
            $this->messagePublisher->reveal(),
            $this->logger->reveal()
        );
        $this->assertInstanceOf('Swarrot\\Processor\\RPC\\RpcServerProcessor', $processor);
    }

    /** @dataProvider noPropertiesProvider */
    public function test_it_should_do_an_early_return_if_no_adequate_properties(array $properties)
    {
        $this->messagePublisher->publish()->shouldNotBeCalled();

        $message = new Message('', $properties);
        $this->assertNull($this->processor->process($message, []));
    }

    public function noPropertiesProvider()
    {
        return [
                [[]],
                [['reply_to' => 'foo']],
                [['correlation_id' => 0]],
                [['reply_to' => '', 'correlation_id' => 0]],
                [['reply_to' => '', 'correlation_id' => 42]],
                [['reply_to' => 'foo', 'correlation_id' => 0]],
                [['headers' => ['reply_to' => '', 'correlation_id' => 42]]]
        ];
    }

    public function test_it_should_publish_a_new_message_when_done()
    {
        $message = new Message('', ['reply_to' => 'foo', 'correlation_id' => 42]);

        $this->parentProcessor->process($message, [])->willReturn('bar');

        $this->messagePublisher->publish(Argument::that(function ($argument) {
            return $argument instanceof Message && 'bar' === $argument->getBody() && array_key_exists('correlation_id', $argument->getProperties());
        }), Argument::is('foo'))->shouldBeCalled();

        $this->assertSame('bar', $this->processor->process($message, []));
    }
}

