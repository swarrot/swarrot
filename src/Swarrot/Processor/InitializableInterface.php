<?php

namespace Swarrot\Processor;

use Swarrot\ParameterBag;

interface InitializableInterface
{
    /**
     * initialize
     *
     * @param ParameterBag $bag
     *
     * @return void
     */
    public function initialize(ParameterBag $bag);
}
