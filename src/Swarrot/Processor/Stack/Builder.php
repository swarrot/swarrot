<?php

namespace Swarrot\Processor\Stack;

class Builder
{
    private $specs;

    public function __construct()
    {
        $this->specs = new \SplStack();
    }

    public function unshift()
    {
        if (func_num_args() === 0) {
            throw new \InvalidArgumentException("Missing argument(s) when calling unshift");
        }

        $spec = func_get_args();
        $this->specs->unshift($spec);

        return $this;
    }

    public function push()
    {
        if (func_num_args() === 0) {
            throw new \InvalidArgumentException("Missing argument(s) when calling push");
        }

        $spec = func_get_args();
        $this->specs->push($spec);

        return $this;
    }

    public function resolve($processor)
    {
        $middlewares = array($processor);

        foreach ($this->specs as $spec) {
            $args = $spec;
            $firstArg = array_shift($args);

            if (is_callable($firstArg)) {
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
