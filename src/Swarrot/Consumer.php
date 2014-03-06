<?php

namespace Swarrot;

use Swarrot\Broker\MessageProviderInterface;
use Swarrot\Broker\Message;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Processor\InitializableInterface;
use Swarrot\Processor\TerminableInterface;
use Swarrot\Processor\SleepyInterface;

class Consumer
{
    protected $messageProvider;
    protected $processor;
    protected $optionsResolver;

    public function __construct(MessageProviderInterface $messageProvider, ProcessorInterface $processor, OptionsResolverInterface $optionsResolver = null)
    {
        $this->messageProvider = $messageProvider;
        $this->processor       = $processor;
        $this->optionsResolver = $optionsResolver ?: new OptionsResolver();
    }

    /**
     * consume
     *
     * @param array $options Parameters sent to the processor
     *
     * @return void
     */
    public function consume(array $options = array())
    {
        $this->optionsResolver->setDefaults(array(
            'poll_interval' => 50000
        ));

        if ($this->processor instanceof ConfigurableInterface) {
            $this->processor->setDefaultOptions($this->optionsResolver);
        }

        $options = $this->optionsResolver->resolve($options);

        if ($this->processor instanceof InitializableInterface) {
            $this->processor->initialize($options);
        }

        while (true) {
            while (null !== $message = $this->messageProvider->get()) {
                if (false === $this->processor->process($message, $options)) {
                    break 2;
                }
            }

            if ($this->processor instanceof SleepyInterface) {
                if (false === $this->processor->sleep($options)) {
                    break;
                }
            }

            usleep($options['poll_interval']);
        }

        if ($this->processor instanceof TerminableInterface) {
            $this->processor->terminate($options);
        }
    }

    public function getMessageProvider()
    {
        return $this->messageProvider;
    }

    public function setMessageProvider(MessageProviderInterface $messageProvider)
    {
        $this->messageProvider = $messageProvider;
    }

    public function getProcessor()
    {
        return $this->processor;
    }

    public function setProcessor($processor)
    {
        $this->processor = $processor;
    }

    public function getOptionsResolver()
    {
        return $this->optionsResolver;
    }

    public function setOptionsResolver(OptionsResolverInterface $optionsResolver)
    {
        $this->optionsResolver = $optionsResolver;
    }
}
