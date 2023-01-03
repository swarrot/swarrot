<?php

namespace Swarrot\Processor;

interface SleepyInterface extends ProcessorInterface
{
    /**
     * This method should return false if the consumer have to stop, true if
     * the consumer should continue to wait for messages.
     *
     * @param array<string, mixed> $options An array containing all parameters
     */
    public function sleep(array $options): bool;
}
