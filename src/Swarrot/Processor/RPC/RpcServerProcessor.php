<?php

namespace Swarrot\Processor\RPC;

use Psr\Log\LoggerInterface;
use Swarrot\Broker\Message;
use Swarrot\Broker\MessagePublisher\MessagePublisherInterface;
use Swarrot\Processor\ProcessorInterface;

/**
 * Act as a RPC server when processing am amqp message.
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
class RpcServerProcessor implements ProcessorInterface
{
    /** @var ProcessorInterface */
    private $processor;

    /** @var MessagePublisherInterface */
    private $publisher;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(ProcessorInterface $processor, MessagePublisherInterface $publisher, LoggerInterface $logger = null)
    {
        $this->processor = $processor;
        $this->publisher = $publisher;
        $this->logger = $logger;
    }

    /** {@inheritdoc} */
    public function process(Message $message, array $options)
    {
        $result = $this->processor->process($message, $options);

        $properties = $message->getProperties();

        if (!isset($properties['reply_to'], $properties['correlation_id']) || empty($properties['reply_to']) || empty($properties['correlation_id'])) {
            return $result;
        }

        $this->logger and $this->logger->info(sprintf('sending a new message to the "%s" queue with the id "%s"', $properties['reply_to'], $properties['correlation_id']), ['swarrot_processor' => 'rpc']);

        $message = new Message((string) $result, ['correlation_id' => $properties['correlation_id']]);
        $this->publisher->publish($message, $properties['reply_to']);

        return $result;
    }
}
