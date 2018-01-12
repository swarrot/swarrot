<?php

namespace Swarrot\Processor\Ack;

use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Broker\MessageProvider\MessageProviderInterface;
use Swarrot\Broker\Message;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AckProcessor implements ConfigurableInterface
{
    /**
     * @var ProcessorInterface
     */
    protected $processor;

    /**
     * @var MessageProviderInterface
     */
    protected $messageProvider;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param ProcessorInterface       $processor       Processor
     * @param MessageProviderInterface $messageProvider Message provider
     * @param LoggerInterface          $logger          Logger
     */
    public function __construct(ProcessorInterface $processor, MessageProviderInterface $messageProvider, LoggerInterface $logger = null)
    {
        $this->processor = $processor;
        $this->messageProvider = $messageProvider;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function process(Message $message, array $options)
    {
        try {
            $return = $this->processor->process($message, $options);
            $this->messageProvider->ack($message);

            $this->logger->info(
                '[Ack] Message #'.$message->getId().' has been correctly ack\'ed',
                [
                    'swarrot_processor' => 'ack',
                    'queue_name' => $this->messageProvider->getQueueName(),
                ]
            );

            return $return;
        } catch (\Throwable $e) {
            $this->handleException($e, $message, $options);
        } catch (\Exception $e) {
            $this->handleException($e, $message, $options);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(array(
                'requeue_on_error' => false,
            ))
            ->setAllowedTypes('requeue_on_error', 'bool')
        ;
    }

    /**
     * @param \Exception|\Throwable $exception
     * @param Message               $message
     * @param array                 $options
     */
    private function handleException($exception, Message $message, array $options)
    {
        $requeue = isset($options['requeue_on_error']) ? (bool) $options['requeue_on_error'] : false;
        $this->messageProvider->nack($message, $requeue);

        $this->logger->error(
            sprintf(
                '[Ack] An exception occurred. Message #%d has been %s.',
                $message->getId(),
                $requeue ? 'requeued' : 'nack\'ed'
            ),
            [
                'swarrot_processor' => 'ack',
                'queue_name' => $this->messageProvider->getQueueName(),
                'exception' => $exception,
            ]
        );

        throw $exception;
    }
}
