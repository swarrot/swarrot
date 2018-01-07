<?php

namespace Swarrot\Processor\Insomniac;

use Swarrot\Broker\MessageInterface;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\SleepyInterface;
use Psr\Log\LoggerInterface;

class InsomniacProcessor implements SleepyInterface
{
    protected $logger;

    /**
     * @var ProcessorInterface
     */
    private $decoratedProcessor;

    public function __construct(ProcessorInterface $decoratedProcessor, LoggerInterface $logger = null)
    {
        $this->decoratedProcessor = $decoratedProcessor;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, array $options)
    {
        return $this->decoratedProcessor->process($message, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function sleep(array $options)
    {
        // Since this should be called after the consumer was not able to retrieve a message,
        // it means that the queue is empty, so we can simply return false to force the consumer to stop
        $this->logger and $this->logger->info(
            '[InsomniacProcessor] No more messages in queue.',
            [
                'swarrot_processor' => 'insomniac',
            ]
        );

        return false;
    }
}
