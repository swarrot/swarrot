<?php

namespace Swarrot\Processor\Retry;

use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Broker\MessageProviderInterface;
use Swarrot\Broker\Message;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Swarrot\Broker\MessagePublisher\MessagePublisherInterface;

class RetryProcessor implements ConfigurableInterface
{
    protected $processor;
    protected $publisher;
    protected $logger;

    public function __construct(ProcessorInterface $processor, MessagePublisherInterface $publisher, LoggerInterface $logger = null)
    {
        $this->processor = $processor;
        $this->publisher = $publisher;
        $this->logger    = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function process(Message $message, array $options)
    {
        try {
            return $this->processor->process($message, $options);
        } catch (\Exception $e) {
            $headers = $message->getHeaders();
            $attempts = isset($headers['swarrot_retry_attempts']) ? (int)$headers['swarrot_retry_attempts']++ : 0;
            $attempts++;

            if ($attempts > $options['retry_attempts']) {
                if (null !== $this->logger) {
                    $this->logger->warning(sprintf(
                        'Stop attempting to process message after %d attempts',
                        $attempts
                    ));
                }

                throw $e;
            }

            $message = new Message(
                $message->getBody(),
                array_merge($message->getHeaders(), array('swarrot_retry_attempts' => $attempts))
            );

            $key = str_replace('%attempt%', $attempts, $options['retry_key_pattern']);

            if (null !== $this->logger) {
                $this->logger->warning(sprintf(
                    'An exception occured. Republish message for the %d times (in %s)',
                    $attempts,
                    $key
                ));
            }

            $this->publisher->publish($message, $key);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'retry_attempts' => 3,
            ))
            ->setRequired(array(
                'retry_key_pattern'
            ))
            ->setAllowedTypes(array(
                'retry_attempts' => 'integer',
                'retry_key_pattern' => 'string',
            ))
        ;
    }
}
