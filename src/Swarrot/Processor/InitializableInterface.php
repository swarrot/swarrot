<?php

namespace Swarrot\Processor;

interface InitializableInterface extends ProcessorInterface
{
    /**
     * @param array<string, mixed> $options An array containing all parameters
     */
    public function initialize(array $options): void;
}
