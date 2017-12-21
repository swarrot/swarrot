<?php

namespace Swarrot\Tests\Processor\NewRelic;

use PHPUnit\Framework\TestCase;
use Swarrot\Processor\ProcessorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Swarrot\Processor\NewRelic\NewRelicProcessor;

class NewRelicProcessorTest extends TestCase
{
    public function testOptions()
    {
        $processor = new NewRelicProcessor(
            $this->prophesize(ProcessorInterface::class)->reveal()
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
