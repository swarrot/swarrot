<?php

namespace Swarrot\Processor\InstantRetry;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Swarrot\Broker\Message;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Processor\ProcessorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InstantRetryProcessor implements ConfigurableInterface
{
    private ProcessorInterface $processor;
    private LoggerInterface $logger;

    public function __construct(ProcessorInterface $processor, LoggerInterface $logger = null)
    {
        $this->processor = $processor;
        $this->logger = $logger ?: new NullLogger();
    }

    public function process(Message $message, array $options): bool
    {
        $retry = 0;

        while ($retry++ < $options['instant_retry_attempts']) {
            try {
                return $this->processor->process($message, $options);
            } catch (\Throwable $e) {
                $logLevel = LogLevel::WARNING;

                foreach ($options['instant_retry_log_levels_map'] as $className => $level) {
                    if ($e instanceof $className) {
                        $logLevel = $level;

                        break;
                    }
                }

                $this->logger->log($logLevel, '[InstantRetry] An exception occurred. The message will be processed again.', [
                    'swarrot_processor' => 'instant_retry',
                    'exception' => $e,
                    'message_id' => $message->getId(),
                    'instant_retry_delay' => $options['instant_retry_delay'] / 1000,
                ]);

                usleep($options['instant_retry_delay']);
            }
        }

        if (isset($e)) {
            throw $e;
        }

        throw new \RuntimeException('You probably misconfigured the InstantRetryProcessor.');
    }

    public function setDefaultOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'instant_retry_delay' => 2000000,
            'instant_retry_attempts' => 3,
            'instant_retry_log_levels_map' => [],
        ]);
    }
}
