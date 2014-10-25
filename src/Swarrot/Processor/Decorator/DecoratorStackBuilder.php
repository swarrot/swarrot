<?php

namespace Swarrot\Processor\Decorator;

use Swarrot\Processor\ProcessorInterface;

class DecoratorStackBuilder
{
    /**
     * @var DecoratorStackFactory
     */
    private $stackFactory;

    private $decorators = [];

    public function __construct(DecoratorStackFactory $stackFactory = null)
    {
        $this->stackFactory = $stackFactory ?: new DecoratorStackFactory;
    }

    public function addDecorator(DecoratorInterface $decorator, $priority = 0)
    {
        $this->decorators[$priority][] = $decorator;
    }

    /**
     * @return DecoratorInterface[] From the lowest priority to the highest
     */
    public function getDecorators()
    {
        ksort($this->decorators);

        $flattenedDecorators = [];
        foreach ($this->decorators as $priority => $currentPriorityDecorators) {
            foreach ($currentPriorityDecorators as $currentPriorityDecorator) {
                $flattenedDecorators[] = $currentPriorityDecorator;
            }
        }

        return $flattenedDecorators;
    }

    public function build(ProcessorInterface $processor)
    {
        return $this->stackFactory->create($processor, $this->getDecorators());
    }
}
