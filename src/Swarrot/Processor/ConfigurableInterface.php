<?php

namespace Swarrot\Processor;

use Symfony\Component\OptionsResolver\OptionsResolver;

interface ConfigurableInterface extends ProcessorInterface
{
    /**
     * @return void
     */
    public function setDefaultOptions(OptionsResolver $resolver);
}
