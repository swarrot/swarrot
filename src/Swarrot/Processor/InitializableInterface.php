<?php

namespace Swarrot\Processor;

interface InitializableInterface extends ProcessorInterface
{
    /**
     * initialize.
     *
     * @param array $options
     */
    public function initialize(array $options);
}
