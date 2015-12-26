<?php

namespace Swarrot\Processor\NewRelic;

use Symfony\Component\OptionsResolver\OptionsResolver;

class NewRelicProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function testOptions()
    {
        $processor = new NewRelicProcessor(
            $this->prophesize('Swarrot\Processor\ProcessorInterface')->reveal()
        );

        $resolver = new OptionsResolver();
        $resolver->setDefaults(['queue' => 'image_crop']);
        $processor->setDefaultOptions($resolver);

        $options = $resolver->resolve([
            'new_relic_app_name' => 'swarrot ftw',
        ]);

        $this->assertArrayHasKey('new_relic_transaction_name', $options);
        $this->assertSame('swarrot image_crop', $options['new_relic_transaction_name']);
    }
}
