<?php

namespace Swarrot\Processor\Ack;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Swarrot\Broker\Message;
use Swarrot\Broker\MessageProvider\MessageProviderInterface;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Processor\ProcessorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @final since 4.16.0
 */
class AckProcessor implements ConfigurableInterface
{
    private ProcessorInterface $processor;
    private MessageProviderInterface $messageProvider;
    private LoggerInterface $logger;

    public function __construct(ProcessorInterface $processor, MessageProviderInterface $messageProvider, LoggerInterface $logger = null)
    {
        $this->processor = $processor;
        $this->messageProvider = $messageProvider;
        $this->logger = $logger ?: new NullLogger();
    }

    public function process(Message $message, array $options): bool
    {
        try {
            $return = $this->processor->process($message, $options);
            $this->messageProvider->ack($message);

            $this->logger->info(
                "[Ack] Message has been correctly ack'ed",
                [
                    'message_id' => $message->getId(),
                    'swarrot_processor' => 'ack',
                ]
            );

            return $return;
        } catch (\Throwable $e) {
            $requeue = isset($options['requeue_on_error']) ? (bool) $options['requeue_on_error'] : false;
            $this->messageProvider->nack($message, $requeue);

            $this->logger->error(
                sprintf(
                    '[Ack] An exception occurred, the message has been %s.',
                    $requeue ? 'requeued' : "nack'ed"
                ),
                [
                    'message_id' => $message->getId(),
                    'swarrot_processor' => 'ack',
                    'exception' => $e,
                ]
            );

            throw $e;
        }
    }

    public function setDefaultOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'requeue_on_error' => false,
            ])
            ->setAllowedTypes('requeue_on_error', 'bool')
        ;
    }
}
