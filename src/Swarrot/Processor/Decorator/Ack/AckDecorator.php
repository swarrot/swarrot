<?php

namespace Swarrot\Processor\Decorator\Ack;

use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Broker\MessageProvider\MessageProviderInterface;
use Swarrot\Broker\Message;
use Psr\Log\LoggerInterface;
use Swarrot\Processor\Decorator\DecoratorInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AckDecorator implements DecoratorInterface, ConfigurableInterface
{
    /**
     * @var MessageProviderInterface
     */
    protected $messageProvider;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     *
     * @param MessageProviderInterface $messageProvider Message provider
     * @param LoggerInterface          $logger          Logger
     */
    public function __construct(MessageProviderInterface $messageProvider, LoggerInterface $logger = null)
    {
        $this->messageProvider = $messageProvider;
        $this->logger          = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function decorate(ProcessorInterface $processor, Message $message, array $options)
    {
        try {
            $return = $processor->process($message, $options);
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
                $this->logger->warning(
                    sprintf(
                        '[Ack] An exception occurred. Message #%d have been %s.',
                        $message->getId(),
                        $requeue ? 'requeued' : 'nack\'ed'
                    ),
                    array(
                        'exception' => $e,
                    )
                );
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
