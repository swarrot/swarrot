<?php

namespace Swarrot\Processor\Stack;

use Swarrot\Processor\ProcessorInterface;

/**
 * @final since 4.16.0
 */
class Builder
{
    /**
     * @var \SplStack
     */
    private $specs;

    public function __construct()
    {
        $this->specs = new \SplStack();
    }

    /**
     * @throws \InvalidArgumentException Missing argument(s) when calling unshift
     */
    public function unshift(): self
    {
        if (0 === \func_num_args()) {
            throw new \InvalidArgumentException('Missing argument(s) when calling unshift');
        }

        $spec = \func_get_args();
        $this->specs->unshift($spec);

        return $this;
    }

    /**
     * @throws \InvalidArgumentException Missing argument(s) when calling push
     */
    public function push(): self
    {
        if (0 === \func_num_args()) {
            throw new \InvalidArgumentException('Missing argument(s) when calling push');
        }

        $spec = \func_get_args();
        $this->specs->push($spec);

        return $this;
    }

    /**
     * @param ProcessorInterface $processor
     */
    public function resolve($processor): StackedProcessor
    {
        $middlewares = [$processor];

        foreach ($this->specs as $spec) {
            $args = $spec;
            $firstArg = array_shift($args);

            if (\is_callable($firstArg)) {
                $processor = $firstArg($processor);
            } else {
                $kernelClass = $firstArg;
                array_unshift($args, $processor);

                $reflection = new \ReflectionClass($kernelClass);
                $processor = $reflection->newInstanceArgs($args);
            }

            array_unshift($middlewares, $processor);
        }

        return new StackedProcessor($processor, $middlewares);
    }
}
