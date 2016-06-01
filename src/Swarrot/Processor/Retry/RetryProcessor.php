<?php

namespace Swarrot\Processor\Retry;

use Swarrot\Processor\InitializableInterface;
use Swarrot\Processor\DecoratorTrait;
use Swarrot\Processor\SleepyInterface;
use Swarrot\Processor\TerminableInterface;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Broker\Message;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Swarrot\Broker\MessagePublisher\MessagePublisherInterface;

class RetryProcessor implements ProcessorInterface, ConfigurableInterface, InitializableInterface, SleepyInterface, TerminableInterface
{
    use DecoratorTrait {
        DecoratorTrait::setDefaultOptions as decoratorOptions;
    }

    protected $publisher;
    protected $logger;

    public function __construct(ProcessorInterface $processor, MessagePublisherInterface $publisher, LoggerInterface $logger = null)
    {
        $this->processor = $processor;
        $this->publisher = $publisher;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Message $message, array $options)
    {
        try {
            return $this->processor->process($message, $options);
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
        $this->decoratorOptions($resolver);

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

    /**
     * @param \Exception|\Throwable $exception
     * @param Message               $message
     * @param array                 $options
     */
    private function handleException($exception, Message $message, array $options)
    {
        $properties = $message->getProperties();

        $attempts = 0;
        if (isset($properties['headers']['swarrot_retry_attempts'])) {
            $attempts = $properties['headers']['swarrot_retry_attempts'];
        }
        ++$attempts;

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

            throw $exception;
        }

        if (!isset($properties['headers'])) {
            $properties['headers'] = array();
        }

        $properties['headers']['swarrot_retry_attempts'] = $attempts;

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
                'exception' => $exception,
            ]
        );

        $this->publisher->publish($message, $key);
    }
}
