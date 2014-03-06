<?php

namespace Swarrot\Processor\Stack;

use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Processor\InitializableInterface;
use Swarrot\Processor\TerminableInterface;
use Swarrot\Processor\SleepyInterface;
use Swarrot\Broker\Message;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class StackedProcessor implements ConfigurableInterface, InitializableInterface, TerminableInterface, SleepyInterface
{
    protected $processor;
    protected $middlewares;

    public function __construct($processor, array $middlewares)
    {
        $this->processor   = $processor;
        $this->middlewares = $middlewares;
    }

    /**
     * setDefaultOptions
     *
     * @param OptionsResolverInterface $resolver
     *
     * @return void
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        foreach ($this->middlewares as $middleware) {
            if ($middleware instanceof ConfigurableInterface) {
                $middleware->setDefaultOptions($resolver);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function initialize(array $options)
    {
        foreach ($this->middlewares as $middleware) {
            if ($middleware instanceof InitializableInterface) {
                $middleware->initialize($options);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(Message $message, array $options)
    {
        return call_user_func_array(
            $this->processor,
            array($message, $options)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function terminate(array $options)
    {
        foreach ($this->middlewares as $middleware) {
            if ($middleware instanceof TerminableInterface) {
                $middleware->terminate($options);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function sleep(array $options)
    {
        foreach ($this->middlewares as $middleware) {
            if ($middleware instanceof SleepyInterface) {
                $middleware->sleep($options);
            }
        }
    }
}
