<?php

namespace Swarrot\Processor;

interface TerminableInterface extends ProcessorInterface
{
    /**
     * terminate
     *
     * @param array $options
     *
     * @return void
     */
    public function terminate(array $options);
}
