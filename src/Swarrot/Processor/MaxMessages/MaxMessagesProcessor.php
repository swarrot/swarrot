<?php

namespace Swarrot\Processor\MaxMessages;

use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Broker\Message;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MaxMessagesProcessor implements ConfigurableInterface
{
    /**
     * @var ProcessorInterface
     */
    protected $processor;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var int
     */
    protected $messagesProcessed = 0;

    /**
     * @param ProcessorInterface $processor Processor
     * @param LoggerInterface    $logger    Logger
     */
    public function __construct(ProcessorInterface $processor, LoggerInterface $logger = null)
    {
        $this->processor = $processor;
        $this->logger    = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function process(Message $message, array $options)
    {
        $return = $this->processor->process($message, $options);

        if (++$this->messagesProcessed >= $options['max_messages']) {
            $this->logger and $this->logger->info(
                sprintf('[MaxMessages] Max messages have been reached (%d)', $options['max_messages']),
                array(
                    'swarrot_processor' => 'max_messages'
                )
            );

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
