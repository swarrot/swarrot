<?php

namespace Swarrot\Processor\Stack;

use Swarrot\Broker\Message;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Processor\InitializableInterface;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\SleepyInterface;
use Swarrot\Processor\TerminableInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StackedProcessor implements ConfigurableInterface, InitializableInterface, TerminableInterface, SleepyInterface
{
    private $processor;
    private $middlewares;

    public function __construct(ProcessorInterface $processor, array $middlewares)
    {
        $this->processor = $processor;
        $this->middlewares = $middlewares;
    }

    /**
     * setDefaultOptions.
     */
    public function setDefaultOptions(OptionsResolver $resolver): void
    {
        foreach ($this->middlewares as $middleware) {
            if ($middleware instanceof ConfigurableInterface) {
                $middleware->setDefaultOptions($resolver);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options): void
    {
        foreach ($this->middlewares as $middleware) {
            if ($middleware instanceof InitializableInterface) {
                $middleware->initialize($options);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function process(Message $message, array $options): bool
    {
        return $this->processor->process($message, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function terminate(array $options): void
    {
        foreach ($this->middlewares as $middleware) {
            if ($middleware instanceof TerminableInterface) {
                $middleware->terminate($options);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function sleep(array $options): bool
    {
        foreach ($this->middlewares as $middleware) {
            if ($middleware instanceof SleepyInterface) {
                if (false === $middleware->sleep($options)) {
                    return false;
                }
            }
        }

        return true;
    }
}
