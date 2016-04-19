<?php
namespace Swarrot\Processor;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Trait for processors to use if they have a children processor
 *
 * This trait is used whenever a processor has a child processor, and thus
 * must implement all interfaces so that its child's specificities (such as
 * Configuration, Sleepy, ...) are called.
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
trait DecoratorTrait
{
    /** @var ProcessorInterface */
    protected $processor;

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        if ($this->processor instanceof ConfigurableInterface) {
            $this->processor->setDefaultOptions($resolver);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        if ($this->processor instanceof InitializableInterface) {
            $this->processor->initialize($options);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function sleep(array $options)
    {
        if ($this->processor instanceof SleepyInterface) {
            return $this->processor->sleep($options);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function terminate(array $options)
    {
        if ($this->processor instanceof TerminableInterface) {
            $this->processor->terminate($options);
        }
    }
}

