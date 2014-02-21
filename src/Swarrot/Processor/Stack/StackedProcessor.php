<?php

namespace Swarrot\Processor\Stack;

use Swarrot\Processor\InitializableInterface;
use Swarrot\Processor\TerminableInterface;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\ParameterBag;
use Swarrot\AMQP\Message;

class StackedProcessor implements InitializableInterface, TerminableInterface, ProcessorInterface
{
    protected $processor;
    protected $middlewares;

    public function __construct($processor, array $middlewares)
    {
        $this->processor   = $processor;
        $this->middlewares = $middlewares;
    }

    /**
     * {@inheritDoc}
     */
    public function initialize(ParameterBag $bag)
    {
        foreach ($this->middlewares as $middleware) {
            if ($middleware instanceof InitializableInterface) {
                $middleware->initialize($bag);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(Message $message, ParameterBag $bag)
    {
        return call_user_func_array(
            $this->processor,
            array($message, $bag)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function terminate(ParameterBag $bag)
    {
        foreach ($this->middlewares as $middleware) {
            if ($middleware instanceof TerminableInterface) {
                $middleware->terminate($bag);
            }
        }
    }
}
