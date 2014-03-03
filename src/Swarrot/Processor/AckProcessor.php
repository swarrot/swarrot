<?php

namespace Swarrot\Processor;

use Swarrot\Processor\ProcessorInterface;
use Swarrot\Broker\MessageProviderInterface;
use Swarrot\Broker\Message;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AckProcessor implements ConfigurableInterface
{
    protected $processor;
    protected $logger;

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
        try {
            $processor($message, $options);
            $this->messageProvider->ack($message);

            if (null !== $this->logger) {
                $this->logger->debug(sprintf(
                    'Message #%d have been correctly ack\'ed',
                    $message->getId()
                ));
            }
        } catch (\Exception $e) {
            $requeue = isset($options['requeue_on_error'])? (boolean) $options['requeue_on_error'] : false;
            $this->messageProvider->nack($message, $requeue);

            if (null !== $this->logger) {
                $this->logger->warning(sprintf(
                    'An exception occured. Message #%d have been nack\'ed. Exception message: "%s"',
                    $message->getId(),
                    $e->getMessage()
                ));
            }

            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'requeue_on_error' => false
        ));

        $resolver->setAllowedValues(array(
            'requeue_on_error' => array(true, false),
        ));
    }
}
