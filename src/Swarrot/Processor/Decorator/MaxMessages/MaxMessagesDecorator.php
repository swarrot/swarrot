<?php

namespace Swarrot\Processor\Decorator\MaxMessages;

use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Broker\Message;
use Psr\Log\LoggerInterface;
use Swarrot\Processor\Decorator\DecoratorInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MaxMessagesDecorator implements DecoratorInterface, ConfigurableInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var int
     */
    protected $messagesProcessed = 0;

    /**
     * @param LoggerInterface $logger Logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function decorate(ProcessorInterface $processor, Message $message, array $options)
    {
        $return = $processor->process($message, $options);

        if (++$this->messagesProcessed >= $options['max_messages']) {
            if (null !== $this->logger) {
                $this->logger->info(sprintf(
                    '[MaxMessages] Max messages have been reached (%d)',
                    $options['max_messages']
                ));
            }

            return false;
        }

        return $return;
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
