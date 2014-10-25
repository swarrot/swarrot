<?php

namespace Swarrot\Processor\Insomniac;

use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\SleepyInterface;

class InsomniacProcessor implements SleepyInterface
{
    /**
     * @var ProcessorInterface
     */
    private $decoratedProcessor;

    public function __construct(ProcessorInterface $decoratedProcessor)
    {
        $this->decoratedProcessor = $decoratedProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Message $message, array $options)
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

        return false;
    }
}
