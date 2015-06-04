<?php

namespace Swarrot\Processor;

use Symfony\Component\OptionsResolver\OptionsResolver;

interface ConfigurableInterface extends ProcessorInterface
{
    /**
     * setDefaultOptions.
     *
     * @param OptionsResolver $resolver
     */
    public function setDefaultOptions(OptionsResolver $resolver);
}
