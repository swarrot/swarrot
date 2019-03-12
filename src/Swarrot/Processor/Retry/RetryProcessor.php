<?php

namespace Swarrot\Processor\Retry;

use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Broker\Message;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Options;
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
        $resolver
            ->setDefaults(array(
                'retry_attempts' => 3,
                'retry_log_levels_map' => array(),
                'retry_fail_log_levels_map' => array(),
                'retry_key_generator' => function (Options $options) {
                    if (!isset($options['retry_key_pattern'])) {
                        throw new MissingOptionsException('Either the retry_key_pattern or retry_key_generator option is required.');
                    }

                    $keyPattern = $options['retry_key_pattern'];

                    return function ($attempts) use ($keyPattern) {
                        return str_replace('%attempt%', $attempts, $keyPattern);
                    };
                },
            ))
            ->setDefined(array(
                'retry_key_pattern', // Mandatory if retry_key_generator is not provided
            ))
            ->setAllowedTypes('retry_attempts', 'int')
            ->setAllowedTypes('retry_key_pattern', 'string')
            ->setAllowedTypes('retry_key_generator', 'callable')
            ->setAllowedTypes('retry_log_levels_map', 'array')
            ->setAllowedTypes('retry_fail_log_levels_map', 'array')
        ;
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
            $this->logger and $this->logException(
                $exception,
                sprintf(
                    '[Retry] Stop attempting to process message after %d attempts',
                    $attempts
                ),
                $options['retry_fail_log_levels_map']
            );

            throw $exception;
        }

        if (!isset($properties['headers'])) {
            $properties['headers'] = array();
        }

        $properties['headers']['swarrot_retry_attempts'] = $attempts;

        $keyGenerator = $options['retry_key_generator'];

        $key = $keyGenerator($attempts, $message);

        $message = new Message(
            $message->getBody(),
            $properties
        );

        $this->logger and $this->logException(
            $exception,
            sprintf(
                '[Retry] An exception occurred. Republish message for the %d times (key: %s)',
                $attempts,
                $key
            ),
            $options['retry_log_levels_map']
        );

        $this->publisher->publish($message, $key);
    }

    /**
     * @param \Exception|\Throwable $exception
     * @param string                $logMessage
     * @param array                 $logLevelsMap
     */
    private function logException($exception, $logMessage, array $logLevelsMap)
    {
        $logLevel = LogLevel::WARNING;

        foreach ($logLevelsMap as $className => $level) {
            if ($exception instanceof $className) {
                $logLevel = $level;

                break;
            }
        }

        $this->logger->log(
            $logLevel,
            $logMessage,
            [
                'swarrot_processor' => 'retry',
                'exception' => $exception,
            ]
        );
    }
}
