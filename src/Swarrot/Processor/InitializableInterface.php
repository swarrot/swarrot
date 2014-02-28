<?php

namespace Swarrot\Processor;

interface InitializableInterface extends ProcessorInterface
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
