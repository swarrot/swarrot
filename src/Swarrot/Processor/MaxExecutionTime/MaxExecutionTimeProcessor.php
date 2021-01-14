<?php

namespace Swarrot\Processor\MaxExecutionTime;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Swarrot\Broker\Message;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Processor\InitializableInterface;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\SleepyInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MaxExecutionTimeProcessor implements ConfigurableInterface, InitializableInterface, SleepyInterface
{
    private $processor;
    private $logger;

    /**
     * @var float
     */
    private $startTime;

    public function __construct(ProcessorInterface $processor, LoggerInterface $logger = null)
    {
        $this->processor = $processor;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'max_execution_time' => 300,
            ])
            ->setAllowedTypes('max_execution_time', 'int')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options): void
    {
        $this->startTime = microtime(true);
    }

    /**
     * {@inheritdoc}
     */
    public function sleep(array $options): bool
    {
        return !$this->isTimeExceeded($options);
    }

    /**
     * {@inheritdoc}
     */
    public function process(Message $message, array $options): bool
    {
        return $this->processor->process($message, $options) && !$this->isTimeExceeded($options);
    }

    protected function isTimeExceeded(array $options): bool
    {
        if (microtime(true) - $this->startTime > $options['max_execution_time']) {
            $this->logger->info('[MaxExecutionTime] Max execution time has been reached', [
                'max_execution_time' => $options['max_execution_time'],
                'swarrot_processor' => 'max_execution_time',
            ]);

            return true;
        }

        return false;
    }
}
