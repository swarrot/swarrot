<?php

namespace Swarrot\Processor\MemoryReset;

use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;

class MemoryResetProcessor implements ProcessorInterface
{
    private ProcessorInterface $processor;

    public function __construct(ProcessorInterface $processor)
    {
        $this->processor = $processor;
    }

    public function process(Message $message, array $options): bool
    {
        $return = $this->processor->process($message, $options);

        if (\PHP_VERSION_ID >= 80200) {
            memory_reset_peak_usage();
        }

        return $return;
    }
}
