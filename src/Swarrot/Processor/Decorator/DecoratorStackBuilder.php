<?php

namespace Swarrot\Processor\Decorator;

use Swarrot\Processor\ProcessorInterface;

class DecoratorStackBuilder
{
    public static function createStack(ProcessorInterface $processor, array $decorators)
    {
        while (null !== ($decorator = array_pop($decorators))) {
            $processor = new DecoratorProcessor($decorator, $processor);
        }

        return $processor;
    }

    private $decorators = [];

    public function addDecorator(DecoratorInterface $decorator, $priority = 0)
    {
        $this->decorators[$priority][] = $decorator;
    }

    public function build(ProcessorInterface $processor)
    {
        ksort($this->decorators);

        $flattenedDecorators = [];
        foreach ($this->decorators as $priority => $currentPriorityDecorators) {
            foreach ($currentPriorityDecorators as $currentPriorityDecorator) {
                $flattenedDecorators[] = $currentPriorityDecorator;
            }
        }

        return static::createStack($processor, $flattenedDecorators);
    }
}
