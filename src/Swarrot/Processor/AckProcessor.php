<?php

namespace Swarrot\Processor;

use Swarrot\Processor\ProcessorInterface;
use Swarrot\Broker\MessageProviderInterface;
use Swarrot\Broker\Message;
use Psr\Log\LoggerInterface;

class AckProcessor implements ProcessorInterface
{
    protected $processor;
    protected $logger;

    public function __construct(ProcessorInterface $processor, MessageProviderInterface $messageProvider, LoggerInterface $logger = null)
    {
        $this->processor       = $processor;
        $this->messageProvider = $messageProvider;
        $this->logger          = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(Message $message, array $options)
    {
        $processor = $this->processor;
        try {
            $processor($message, $options);
            $this->messageProvider->ack($message);

            if (null !== $this->logger) {
                $this->logger->debug(sprintf(
                    'Message #%d have been correctly ack\'ed',
                    $message->getId()
                ));
            }
        } catch (\Exception $e) {
            $this->messageProvider->nack($message);

            if (null !== $this->logger) {
                $this->logger->warning(sprintf(
                    'An exception occured. Message #%d have been nack\'ed. Exception message: "%s"',
                    $message->getId(),
                    $e->getMessage()
                ));
            }
        }
    }
}
