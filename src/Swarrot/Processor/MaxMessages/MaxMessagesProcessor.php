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
    private $processor;
    private $logger;

    /**
     * @var int
     */
    private $messagesProcessed = 0;

    public function __construct(ProcessorInterface $processor, LoggerInterface $logger = null)
    {
        $this->processor = $processor;
        $this->logger = $logger ?: new NullLogger();
    }

    public function process(Message $message, array $options): bool
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

    public function setDefaultOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'max_messages' => 100,
            ])
            ->setAllowedTypes('max_messages', 'int')
        ;
    }
}
