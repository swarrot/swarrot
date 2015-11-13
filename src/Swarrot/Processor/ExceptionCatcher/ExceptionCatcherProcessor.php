<?php

namespace Swarrot\Processor\ExceptionCatcher;

use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ExceptionCatcherProcessor
 *
 * @package Swarrot\Processor\ExceptionCatcher
 */
class ExceptionCatcherProcessor implements ProcessorInterface
{
    /**
     * @var ProcessorInterface
     */
    protected $processor;

    /**
     * @var LoggerInterface
     */
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
            $this->logger and $this->logger->error(
                '[ExceptionCatcher] An exception occurred. This exception has been caught.',
                [
                    'swarrot_processor' => 'exception',
                    'exception'         => $e,
                ]
            );
        }

        return;
    }
}
