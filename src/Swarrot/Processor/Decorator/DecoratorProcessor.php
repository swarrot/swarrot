<?php

namespace Swarrot\Processor\Decorator;

use Swarrot\Broker\Message;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Processor\InitializableInterface;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\SleepyInterface;
use Swarrot\Processor\TerminableInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DecoratorProcessor implements ProcessorInterface, ConfigurableInterface, InitializableInterface, SleepyInterface, TerminableInterface
{
    /**
     * @var DecoratorInterface
     */
    private $decorator;

    /**
     * @var ProcessorInterface
     */
    private $processor;

    public function __construct(DecoratorInterface $decorator, ProcessorInterface $processor)
    {
        $this->decorator = $decorator;
        $this->processor = $processor;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Message $message, array $options)
    {
        return $this->decorator->decorate($this->processor, $message, $options);
    }

    /**
     * @return DecoratorInterface
     */
    public function getDecorator()
    {
        return $this->decorator;
    }

    /**
     * @return ProcessorInterface
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        foreach ([$this->decorator, $this->processor] as $object) {
            if ($object instanceof ConfigurableInterface) {
                $object->setDefaultOptions($resolver);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        foreach ([$this->decorator, $this->processor] as $object) {
            if ($object instanceof InitializableInterface) {
                $object->initialize($options);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function sleep(array $options)
    {
        $sleep = false;

        foreach ([$this->decorator, $this->processor] as $object) {
            if ($object instanceof SleepyInterface) {
                $sleep = $object->sleep($options) || $sleep;
            }
        }

        return $sleep;
    }

    /**
     * {@inheritdoc}
     */
    public function terminate(array $options)
    {
        foreach ([$this->decorator, $this->processor] as $object) {
            if ($object instanceof TerminableInterface) {
                $object->terminate($options);
            }
        }
    }
}
