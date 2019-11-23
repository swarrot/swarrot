<?php

namespace Swarrot\Processor;

use Symfony\Component\OptionsResolver\OptionsResolver;

interface ConfigurableInterface extends ProcessorInterface
{
    /**
     * setDefaultOptions.
     */
    public function setDefaultOptions(OptionsResolver $resolver);
}
