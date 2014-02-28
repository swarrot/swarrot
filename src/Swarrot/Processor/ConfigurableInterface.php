<?php

namespace Swarrot\Processor;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

interface ConfigurableInterface extends ProcessorInterface
{
    /**
     * setDefaultOptions
     *
     * @param OptionsResolverInterface $resolver
     *
     * @return void
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver);
}
