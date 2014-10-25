<?php

namespace Swarrot\Processor\Decorator;

use Swarrot\Processor\ProcessorInterface;

class DecoratorStackFactory
{
    public function create(ProcessorInterface $processor, array $decorators)
    {
        while (null !== ($decorator = array_pop($decorators))) {
            $processor = new DecoratorProcessor($decorator, $processor);
        }

        return $processor;
    }
}
