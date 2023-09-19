<?php

namespace Swarrot\Processor\Insomniac;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\SleepyInterface;

/**
 * @final since 4.16.0
 */
class InsomniacProcessor implements SleepyInterface
{
    private $processor;
    private $logger;

    public function __construct(ProcessorInterface $processor, LoggerInterface $logger = null)
    {
        $this->processor = $processor;
        $this->logger = $logger ?? new NullLogger();
    }

    public function process(Message $message, array $options): bool
    {
        return $this->processor->process($message, $options);
    }

    public function sleep(array $options): bool
    {
        // Since this should be called after the consumer was not able to retrieve a message,
        // it means that the queue is empty, so we can simply return false to force the consumer to stop
        $this->logger->info(
            '[InsomniacProcessor] No more messages in queue.',
            [
                'swarrot_processor' => 'insomniac',
            ]
        );

        return false;
    }
}
