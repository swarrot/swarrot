<?php

namespace Swarrot\Processor;

use Symfony\Component\OptionsResolver\OptionsResolver;

interface ConfigurableInterface extends ProcessorInterface
{
    public function setDefaultOptions(OptionsResolver $resolver): void;
}
