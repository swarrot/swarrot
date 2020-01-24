<?php

namespace Swarrot\Processor;

interface TerminableInterface extends ProcessorInterface
{
    /**
     * @return void
     */
    public function terminate(array $options);
}
