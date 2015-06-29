<?php

namespace Swarrot\Processor\Retry;

use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Broker\Message;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
            $properties = $message->getProperties();

            $attempts = 0;
            if (isset($properties['headers']['swarrot_retry_attempts'])) {
                $attempts = $properties['headers']['swarrot_retry_attempts'];
            }
            $attempts++;

            if ($attempts > $options['retry_attempts'] && !$options['keep_retrying']) {
                $this->logger and $this->logger->warning(
                    sprintf(
                        '[Retry] Stop attempting to process message after %d attempts',
                        $attempts
                    ),
                    [
                        'swarrot_processor' => 'retry',
                    ]
                );

                throw $e;
            }

            $properties['headers'] = array(
                'swarrot_retry_attempts' => $attempts,
            );

            $message = new Message(
                $message->getBody(),
                $properties
            );

            $key = str_replace(
                '%attempt%',
                ($attempts > $options['retry_attempts']) ? $options['retry_attempts'] : $attempts,
                $options['retry_key_pattern']
            );

            $this->logger and $this->logger->warning(
                sprintf(
                    '[Retry] An exception occurred. Republish message for the %d times (key: %s)',
                    $attempts,
                    $key
                ),
                [
                    'swarrot_processor' => 'retry',
                    'exception' => $e,
                ]
            );

            $this->publisher->publish($message, $key);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(array(
                'retry_attempts' => 3,
                'keep_retrying'  => false
            ))
            ->setRequired(array(
                'retry_key_pattern',
            ))
        ;

        if (method_exists($resolver, 'setDefined')) {
            $resolver->setAllowedTypes('retry_attempts', 'int');
            $resolver->setAllowedTypes('keep_retrying', 'boolean');
            $resolver->setAllowedTypes('retry_key_pattern', 'string');
        } else {
            // BC for OptionsResolver < 2.6
            $resolver->setAllowedTypes(array(
                'retry_attempts' => 'int',
                'keep_retrying' => 'boolean',
                'retry_key_pattern' => 'string',
            ));
        }
    }
}
