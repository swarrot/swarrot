<?php

namespace Swarrot\Processor\ExceptionCatcher;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;

/**
 * @final since 4.16.0
 */
class ExceptionCatcherProcessor implements ProcessorInterface
{
    private ProcessorInterface $processor;
    private LoggerInterface $logger;

    public function __construct(ProcessorInterface $processor, LoggerInterface $logger = null)
    {
        $this->processor = $processor;
        $this->logger = $logger ?: new NullLogger();
    }

    public function process(Message $message, array $options): bool
    {
        try {
            return $this->processor->process($message, $options);
        } catch (\Throwable $e) {
            $this->logger->error(
                '[ExceptionCatcher] An exception occurred. This exception has been caught.',
                [
                    'swarrot_processor' => 'exception',
                    'exception' => $e,
                ]
            );
        }

        return true;
    }
}
