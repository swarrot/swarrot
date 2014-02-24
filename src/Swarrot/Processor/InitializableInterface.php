<?php

namespace Swarrot\Processor;

interface InitializableInterface
{
    /**
     * initialize
     *
     * @param array $options
     *
     * @return void
     */
    public function initialize(array $options);
}
