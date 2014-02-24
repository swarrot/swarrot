<?php

namespace Swarrot;

use Swarrot\AMQP\MessageProviderInterface;
use Swarrot\AMQP\Message;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Consumer
{
    protected $messageProvider;
    protected $optionsResolver;

    public function __construct(MessageProviderInterface $messageProvider, OptionsResolverInterface $optionsResolver = null)
    {
        $this->messageProvider = $messageProvider;

        if (null === $optionsResolver) {
            $optionsResolver = new OptionsResolver();
        }
        $this->optionsResolver = $optionsResolver;
    }

    /**
     * consume
     *
     * @param callable $processor The processor to call
     * @param array    $options   Parameters sent to the processor
     *
     * @return void
     */
    public function consume($processor, array $options = null)
    {
        $this->optionsResolver->setDefault('poll_interval', 50000);

        $this->getDefaultOptions($this->optionsResolver);

        $options = $this->optionsResolver->resolve($options);

        $continue = true;
        while ($continue) {
            $message = $this->messageProvider->get();

            if (null !== $message) {
                $continue = $processor($message, $options);
            } else {
                usleep($options['poll_interval']);
            }
        }

        if ($processor instanceof TerminableInterface) {
            $processor->terminate($options);
        }
    }
}
