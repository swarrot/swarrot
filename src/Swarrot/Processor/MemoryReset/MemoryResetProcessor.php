<?php

namespace Swarrot\Processor\MemoryReset;

use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;

final class MemoryResetProcessor implements ProcessorInterface
{
    private ProcessorInterface $processor;

    public function __construct(ProcessorInterface $processor)
    {
        $this->processor = $processor;
    }

    public function process(Message $message, array $options): bool
    {
        $return = $this->processor->process($message, $options);

        memory_reset_peak_usage();

        return $return;
    }
}
