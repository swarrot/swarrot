<?php

namespace Swarrot\Processor;

use Swarrot\Processor\ProcessorInterface;
use Swarrot\Broker\MessageProviderInterface;
use Swarrot\Broker\Message;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MaxMessagesProcessor implements ConfigurableInterface
{
    protected $processor;
    protected $logger;
    protected $messagesProcessed = 0;

    public function __construct(ProcessorInterface $processor, MessageProviderInterface $messageProvider, LoggerInterface $logger = null)
    {
        $this->processor       = $processor;
        $this->messageProvider = $messageProvider;
        $this->logger          = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(Message $message, array $options)
    {
        $processor = $this->processor;

        if (++$this->messagesProcessed > $options['max_messages']) {
            if (null !== $this->logger) {
                $this->logger->debug(sprintf(
                    'Max messages have been reached (%d)',
                    $options['max_messages']
                ));
            }


            return false;
        }

        return $processor($message, $options);
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
