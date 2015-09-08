<?php

namespace Swarrot\Processor\NewRelic;

use Swarrot\Broker\Message;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Processor\ProcessorInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewRelicProcessor implements ConfigurableInterface
{
    /**
     * @var ProcessorInterface
     */
    private $processor;

    /**
     * @var bool
     */
    private $extensionLoaded;

    public function __construct(ProcessorInterface $processor)
    {
        $this->extensionLoaded = extension_loaded('newrelic');
        $this->processor = $processor;

        if ($this->extensionLoaded) {
            newrelic_end_transaction(true); // stop the current transaction and do not send data
        }
    }

    /**
     * {@inheritdoc}
     */
    public function process(Message $message, array $options)
    {
        if ($this->extensionLoaded) {
            newrelic_start_transaction($options['new_relic_app_name'], $options['new_relic_license']);
            newrelic_name_transaction($options['new_relic_transaction_name']);
            newrelic_background_job(true);
        }

        try {
            $result = $this->processor->process($message, $options);
        } catch (\Exception $e) {
            if ($this->extensionLoaded) {
                newrelic_notice_error(null, $e);
                newrelic_end_transaction();
            }

            throw $e;
        }

        if ($this->extensionLoaded) {
            newrelic_end_transaction();
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'new_relic_app_name',
        ]);
        $resolver->setDefaults([
            'new_relic_license' => null,
            'new_relic_transaction_name' => function (Options $options) {
                return sprintf('swarrot %s', $options['queue']);
            },
        ]);
    }
}
