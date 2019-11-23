<?php

namespace Swarrot\Processor\MaxMessages;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Swarrot\Broker\Message;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Processor\ProcessorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function process(Message $message, array $options)
    {
        $return = $this->processor->process($message, $options);

        if (++$this->messagesProcessed >= $options['max_messages']) {
            $this->logger->info(
                '[MaxMessages] The maximum number of messages has been reached',
                [
                    'max_messages' => $options['max_messages'],
                    'swarrot_processor' => 'max_messages',
                ]
            );

            return false;
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'max_messages' => 100,
            ])
            ->setAllowedTypes('max_messages', 'int')
        ;
    }
}
