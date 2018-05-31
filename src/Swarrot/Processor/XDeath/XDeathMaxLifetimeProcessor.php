<?php

namespace Swarrot\Processor\XDeath;

use PhpAmqpLib\Wire\AMQPArray;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\ConfigurableInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class XDeathMaxLifetimeProcessor implements ConfigurableInterface
{
    /**
     * @var ProcessorInterface
     */
    private $processor;

    /**
     * @var string
     */
    private $queueName;

    /**
     * @var callable
     */
    private $callback;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ProcessorInterface   $processor
     * @param string               $queueName
     * @param callable             $callback
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        ProcessorInterface $processor,
        string $queueName,
        callable $callback,
        LoggerInterface $logger = null
    ) {
        $this->processor = $processor;
        $this->queueName = $queueName;
        $this->callback = $callback;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(array(
                'x_death_max_lifetime' => 3600,
                'x_death_max_lifetime_log_levels_map' => array(),
                'x_death_max_lifetime_fail_log_levels_map' => array(),
            ))
            ->setAllowedTypes('x_death_max_lifetime', 'int')
            ->setAllowedTypes('x_death_max_lifetime_log_levels_map', 'array')
            ->setAllowedTypes('x_death_max_lifetime_fail_log_levels_map', 'array');
    }

    /**
     * {@inheritdoc}
     */
    public function process(Message $message, array $options)
    {
        try {
            return $this->processor->process($message, $options);
        } catch (\Throwable $e) {
            return $this->handleException($e, $message, $options);
        } catch (\Exception $e) {
            return $this->handleException($e, $message, $options);
        }
    }

    /**
     * @param \Exception|\Throwable $exception
     * @param Message               $message
     * @param array                 $options
     *
     * @return mixed
     */
    private function handleException($exception, Message $message, array $options)
    {
        $headers = $message->getProperties();
        if (isset($headers['headers']['x-death'])) {
            $xDeathHeaders = $headers['headers']['x-death'];
            // PhpAmqpLib compatibility
            if ($xDeathHeaders instanceof AMQPArray) {
                $xDeathHeaders = $headers['headers']['x-death']->getNativeData();
            }

            $queueXDeathHeader = null;
            foreach ($xDeathHeaders as $xDeathHeader) {
                if (isset($xDeathHeader['queue']) && $xDeathHeader['queue'] === $this->queueName) {
                    $queueXDeathHeader = $xDeathHeader;
                    break;
                }
            }

            if (null === $queueXDeathHeader) {
                $this->logException(
                    $exception,
                    sprintf(
                        '[XDeathMaxLifetime] No x-death header found for queue name "%s". Do nothing.',
                        $this->queueName
                    ),
                    $options['x_death_max_lifetime_fail_log_levels_map']
                );
            } elseif (isset($queueXDeathHeader['time'])) {
                $xDeathTimestamp = $queueXDeathHeader['time'];
                if (method_exists($xDeathTimestamp, 'getTimestamp')) {
                    $xDeathTimestamp = $xDeathTimestamp->getTimestamp();
                }
                $remainLifetime = $xDeathTimestamp - (time() - $options['x_death_max_lifetime']);
                if ($remainLifetime <= 0) {
                    $this->logException(
                        $exception,
                        sprintf(
                            '[XDeathMaxLifetime] Max lifetime reached. %s/%s seconds exceed. Execute the configured callback.',
                            abs($remainLifetime),
                            $options['x_death_max_lifetime']
                        ),
                        $options['x_death_max_lifetime_fail_log_levels_map']
                    );

                    if (null !== $return = \call_user_func($this->callback, $exception, $message, $options)) {
                        return $return;
                    }
                } else {
                    $this->logException(
                        $exception,
                        sprintf(
                            '[XDeathMaxLifetime] Lifetime remain %d/%d seconds.',
                            $remainLifetime,
                            $options['x_death_max_lifetime']
                        ),
                        $options['x_death_max_lifetime_log_levels_map']
                    );
                }
            }
        }

        throw $exception;
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
                'swarrot_processor' => 'x_death_max_lifetime',
                'exception' => $exception,
            ]
        );
    }
}
