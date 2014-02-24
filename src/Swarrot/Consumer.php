<?php

namespace Swarrot;

use Swarrot\AMQP\MessageProviderInterface;
use Swarrot\AMQP\Message;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Consumer
{
    protected $messageProvider;
    protected $processor;
    protected $optionsResolver;

    public function __construct(MessageProviderInterface $messageProvider, $processor, OptionsResolverInterface $optionsResolver = null)
    {
        $this->messageProvider = $messageProvider;
        $this->processor       = $processor;

        if (null === $optionsResolver) {
            $optionsResolver = new OptionsResolver();
        }
        $this->optionsResolver = $optionsResolver;
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

        $options = $this->optionsResolver->resolve($options);

        $processor = $this->processor;

        if ($processor instanceof InitializableInterface) {
            $processor->initialize($this->optionsResolver);
        }

        $continue = true;
        while ($continue) {
            $message = $this->messageProvider->get();

            if (null !== $message) {
                $return = $processor($message, $options);
                $continue = false !== $return;
            } else {
                usleep($options['poll_interval']);
            }
        }

        if ($processor instanceof TerminableInterface) {
            $processor->terminate($options);
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
