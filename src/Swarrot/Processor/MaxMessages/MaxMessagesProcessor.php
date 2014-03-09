<?php

namespace Swarrot\Processor\MaxMessages;

use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Broker\Message;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MaxMessagesProcessor implements ConfigurableInterface
{
    protected $processor;
    protected $logger;
    protected $messagesProcessed = 0;

    public function __construct(ProcessorInterface $processor, LoggerInterface $logger = null)
    {
        $this->processor       = $processor;
        $this->logger          = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function process(Message $message, array $options)
    {
        if (++$this->messagesProcessed > $options['max_messages']) {
            if (null !== $this->logger) {
                $this->logger->info(sprintf(
                    '[MaxMessages] Max messages have been reached (%d)',
                    $options['max_messages']
                ));
            }

            return false;
        }

        return $this->processor->process($message, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'max_messages' => 100
        ));

        $resolver->setAllowedTypes(array(
            'max_messages' => 'integer',
        ));
    }
}
