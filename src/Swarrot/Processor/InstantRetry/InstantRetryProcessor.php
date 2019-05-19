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
    /**
     * @var ProcessorInterface
     */
    protected $processor;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(ProcessorInterface $processor, LoggerInterface $logger = null)
    {
        $this->processor = $processor;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function process(Message $message, array $options)
    {
        $retry = 0;

        while ($retry++ < $options['instant_retry_attempts']) {
            try {
                return $this->processor->process($message, $options);
            } catch (\Throwable $e) {
                $this->handleException($e, $message, $options);
            } catch (\Exception $e) {
                $this->handleException($e, $message, $options);
            }
        }

        throw $e;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'instant_retry_delay' => 2000000,
            'instant_retry_attempts' => 3,
            'instant_retry_log_levels_map' => array(),
        ));
    }

    /**
     * @param \Exception|\Throwable $exception
     * @param Message               $message
     * @param array                 $options
     */
    private function handleException($exception, Message $message, array $options)
    {
        $this->logException(
            $exception,
            '[InstantRetry] An exception occurred. The message will be processed again.',
            $options['instant_retry_log_levels_map'],
            [
                'message_id' => $message->getId(),
                'instant_retry_delay' => $options['instant_retry_delay'] / 1000,
            ]
        );

        usleep($options['instant_retry_delay']);
    }

    /**
     * @param \Exception|\Throwable $exception
     */
    private function logException(
        $exception,
        $logMessage,
        array $logLevelsMap,
        array $extraContext
    ) {
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
                'swarrot_processor' => 'instant_retry',
                'exception' => $exception,
            ] + $extraContext
        );
    }
}
