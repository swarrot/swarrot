<?php

namespace Swarrot\Processor\Ack;

use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Broker\MessageProviderInterface;
use Swarrot\Broker\Message;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AckProcessor implements ConfigurableInterface
{
    protected $processor;
    protected $messageProvider;
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
    public function process(Message $message, array $options)
    {
        try {
            $return = $this->processor->process($message, $options);
            $this->messageProvider->ack($message);

            if (null !== $this->logger) {
                $this->logger->info(sprintf(
                    '[Ack] Message #%d have been correctly ack\'ed',
                    $message->getId()
                ));
            }

            return $return;
        } catch (\Exception $e) {
            $requeue = isset($options['requeue_on_error'])? (boolean) $options['requeue_on_error'] : false;
            $this->messageProvider->nack($message, $requeue);

            if (null !== $this->logger) {
                $this->logger->warning(sprintf(
                    '[Ack] An exception occured. Message #%d have been nack\'ed. Exception message: "%s"',
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
        $resolver
            ->setDefaults(array(
                'requeue_on_error' => false
            ))
            ->setAllowedTypes(array(
                'requeue_on_error' => 'bool',
            ))
        ;
    }
}
