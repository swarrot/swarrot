<?php

namespace Swarrot\Processor\Ack;

use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Broker\MessageProvider\MessageProviderInterface;
use Swarrot\Broker\Message;
use Psr\Log\LoggerInterface;
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
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Message $message, array $options)
    {
        try {
            $return = $this->processor->process($message, $options);
            $this->messageProvider->ack($message);

            $this->logger and $this->logger->info(
                '[Ack] Message #'.$message->getId().' has been correctly ack\'ed',
                [
                    'swarrot_processor' => 'ack',
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
        $resolver->setDefaults(array(
            'requeue_on_error' => false,
        ));

        if (method_exists($resolver, 'setDefined')) {
            $resolver->setAllowedTypes('requeue_on_error', 'bool');
        } else {
            // BC for OptionsResolver < 2.6
            $resolver->setAllowedTypes(array(
                'requeue_on_error' => 'bool',
            ));
        }
    }

    /**
     * @param \Exception|\Throwable $exception
     * @param Message               $message
     * @param array                 $options
     */
    private function handleException($exception, Message $message, array $options)
    {
        $requeue = isset($options['requeue_on_error']) ? (boolean) $options['requeue_on_error'] : false;
        $this->messageProvider->nack($message, $requeue);

        $this->logger and $this->logger->error(
            sprintf(
                '[Ack] An exception occurred. Message #%d has been %s.',
                $message->getId(),
                $requeue ? 'requeued' : 'nack\'ed'
            ),
            [
                'swarrot_processor' => 'ack',
                'exception' => $exception,
            ]
        );

        throw $exception;
    }
}
