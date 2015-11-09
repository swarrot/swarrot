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
     * {@inheritdoc}
     */
    public function process(Message $message, array $options)
    {
        try {
            return $this->processor->process($message, $options);
        } catch (\Exception $e) {
            $this->onProcessorException($e, $message, $options);
        }
    }

    /**
     * Retry on exception.
     * Retries are stopped once retry_attempts has been reached.
     *
     * @param \Exception $e
     * @param Message    $message
     * @param array      $options
     */
    protected function onProcessorException(\Exception $e, Message $message, array $options)
    {
        $properties = $message->getProperties();
        $attempts = 0;
        if (isset($properties['headers']['swarrot_retry_attempts'])) {
            $attempts = $properties['headers']['swarrot_retry_attempts'];
        }
        $attempts++;
        if ($attempts > $options['retry_attempts']) {
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
        // workaround for https://github.com/pdezwart/php-amqp/issues/170. See https://github.com/swarrot/swarrot/issues/103
        if (isset($properties['delivery_mode']) && 0 === $properties['delivery_mode']) {
            unset($properties['delivery_mode']);
        }
        $message = new Message(
            $message->getBody(),
            $properties
        );
        $key = str_replace('%attempt%', $attempts, $options['retry_key_pattern']);
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

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(array(
                'retry_attempts' => 3,
            ))
            ->setRequired(array(
                'retry_key_pattern',
            ))
        ;

        if (method_exists($resolver, 'setDefined')) {
            $resolver->setAllowedTypes('retry_attempts', 'int');
            $resolver->setAllowedTypes('retry_key_pattern', 'string');
        } else {
            // BC for OptionsResolver < 2.6
            $resolver->setAllowedTypes(array(
                'retry_attempts' => 'int',
                'retry_key_pattern' => 'string',
            ));
        }
    }
}
