<?php

namespace Swarrot\Processor;

interface InitializableInterface extends ProcessorInterface
{
    /**
     * @return void
     */
    public function initialize(array $options);
}
