<?php

namespace Swarrot\Processor;

interface SleepyInterface extends ProcessorInterface
{
    /**
     * sleep
     *
     * @param array $options
     *
     * @return void
     */
    public function sleep(array $options);
}
