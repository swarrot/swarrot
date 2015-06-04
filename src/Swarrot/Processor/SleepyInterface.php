<?php

namespace Swarrot\Processor;

interface SleepyInterface extends ProcessorInterface
{
    /**
     * sleep.
     *
     * This method should return false if the consumer have to stop, true if
     * the consumer should continue to wait for messages.
     *
     * @param array $options
     *
     * @return bool
     */
    public function sleep(array $options);
}
