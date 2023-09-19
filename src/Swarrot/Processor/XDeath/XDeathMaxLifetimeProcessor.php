<?php

namespace Swarrot\Processor\XDeath;

use PhpAmqpLib\Wire\AMQPArray;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Swarrot\Broker\Message;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Processor\ProcessorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class XDeathMaxLifetimeProcessor implements ConfigurableInterface
{
    private $processor;
    private $queueName;
    private $callback;
    private $logger;

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

    public function setDefaultOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'x_death_max_lifetime' => 3600,
                'x_death_max_lifetime_log_levels_map' => [],
                'x_death_max_lifetime_fail_log_levels_map' => [],
            ])
            ->setAllowedTypes('x_death_max_lifetime', 'int')
            ->setAllowedTypes('x_death_max_lifetime_log_levels_map', 'array')
            ->setAllowedTypes('x_death_max_lifetime_fail_log_levels_map', 'array');
    }

    public function process(Message $message, array $options): bool
    {
        try {
            return $this->processor->process($message, $options);
        } catch (\Throwable $e) {
            $headers = $message->getProperties();
            if (!isset($headers['headers']['x-death'])) {
                throw $e;
            }

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
                    $e,
                    sprintf(
                        '[XDeathMaxLifetime] No x-death header found for queue name "%s". Do nothing.',
                        $this->queueName
                    ),
                    $options['x_death_max_lifetime_fail_log_levels_map']
                );

                throw $e;
            }

            if (!isset($queueXDeathHeader['time'])) {
                throw $e;
            }

            $xDeathTimestamp = $queueXDeathHeader['time'];
            if ($xDeathTimestamp instanceof \DateTime || $xDeathTimestamp instanceof \AMQPTimestamp) {
                $xDeathTimestamp = $xDeathTimestamp->getTimestamp();
            }
            $remainLifetime = $xDeathTimestamp - (time() - $options['x_death_max_lifetime']);
            if ($remainLifetime > 0) {
                $this->logException(
                    $e,
                    sprintf(
                        '[XDeathMaxLifetime] Lifetime remain %d/%d seconds.',
                        $remainLifetime,
                        $options['x_death_max_lifetime']
                    ),
                    $options['x_death_max_lifetime_log_levels_map']
                );

                throw $e;
            }

            $this->logException(
                $e,
                sprintf(
                    '[XDeathMaxLifetime] Max lifetime reached. %s/%s seconds exceed. Execute the configured callback.',
                    abs($remainLifetime),
                    $options['x_death_max_lifetime']
                ),
                $options['x_death_max_lifetime_fail_log_levels_map']
            );

            if (null !== $return = \call_user_func($this->callback, $e, $message, $options)) {
                return $return;
            }

            throw $e;
        }
    }

    private function logException(\Throwable $exception, string $logMessage, array $logLevelsMap): void
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
