<?php

namespace Swarrot\Processor;

interface TerminableInterface extends ProcessorInterface
{
    /**
     * terminate.
     *
     * @param array $options
     */
    public function terminate(array $options);
}
