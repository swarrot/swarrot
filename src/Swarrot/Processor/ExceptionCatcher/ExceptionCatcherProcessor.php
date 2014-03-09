<?php

namespace Swarrot\Processor\ExceptionCatcher;

use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;
use Psr\Log\LoggerInterface;

class ExceptionCatcherProcessor implements ProcessorInterface
{
    protected $processor;
    protected $logger;

    public function __construct(ProcessorInterface $processor, LoggerInterface $logger = null)
    {
        $this->processor = $processor;
        $this->logger    = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function process(Message $message, array $options)
    {
        try {
            return $this->processor->process($message, $options);
        } catch (\Exception $e) {
            if (null !== $this->logger) {
                $this->logger->warning(sprintf(
                    '[ExceptionCatcher] An exception occurred. This exception have been catch. Exception message: %s',
                    $e->getMessage()
                ));
            }
        }

        return;
    }
}
