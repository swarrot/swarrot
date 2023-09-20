<?php

namespace Swarrot\Processor\Stack;

use Swarrot\Broker\Message;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Processor\InitializableInterface;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\SleepyInterface;
use Swarrot\Processor\TerminableInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @final since 4.16.0
 */
class StackedProcessor implements ConfigurableInterface, InitializableInterface, TerminableInterface, SleepyInterface
{
    private ProcessorInterface $processor;
    private array $middlewares;

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

    public function initialize(array $options): void
    {
        foreach ($this->middlewares as $middleware) {
            if ($middleware instanceof InitializableInterface) {
                $middleware->initialize($options);
            }
        }
    }

    public function process(Message $message, array $options): bool
    {
        return $this->processor->process($message, $options);
    }

    public function terminate(array $options): void
    {
        foreach ($this->middlewares as $middleware) {
            if ($middleware instanceof TerminableInterface) {
                $middleware->terminate($options);
            }
        }
    }

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
