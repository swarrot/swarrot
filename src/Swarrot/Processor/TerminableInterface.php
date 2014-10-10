<?php

namespace Swarrot\Processor;

interface TerminableInterface
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
