<?php

namespace Swarrot\Processor;

use Swarrot\ParameterBag;

interface TerminableInterface
{
    /**
     * terminate
     *
     * @param ParameterBag $bag
     *
     * @return void
     */
    public function terminate(ParameterBag $bag);
}
