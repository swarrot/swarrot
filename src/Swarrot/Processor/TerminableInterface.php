<?php

namespace Swarrot\Processor;

interface TerminableInterface extends ProcessorInterface
{
    public function terminate(array $options): void;
}
