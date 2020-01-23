<?php

namespace Swarrot\Processor;

interface InitializableInterface extends ProcessorInterface
{
    public function initialize(array $options): void;
}
