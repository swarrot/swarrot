<?php

namespace Swarrot\Processor;

use Symfony\Component\OptionsResolver\OptionsResolver;

interface ConfigurableInterface extends ProcessorInterface
{
    /**
     * setDefaultOptions
     *
     * @param OptionsResolver $resolver
     *
     * @return void
     */
    public function setDefaultOptions(OptionsResolver $resolver);
}
