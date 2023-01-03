<?php

namespace Swarrot\Processor;

interface TerminableInterface extends ProcessorInterface
{
    /**
     * @param array<string, mixed> $options An array containing all parameters
     */
    public function terminate(array $options): void;
}
