<?php

namespace Swarrot\Processor;

interface InitializableInterface extends ProcessorInterface
{
    /**
     * initialize.
     */
    public function initialize(array $options);
}
