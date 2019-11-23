<?php

namespace Swarrot\Processor;

interface TerminableInterface extends ProcessorInterface
{
    /**
     * terminate.
     */
    public function terminate(array $options);
}
