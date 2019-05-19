<?php

namespace Swarrot\Processor\MaxExecutionTime;

use Swarrot\Processor\ProcessorInterface;
use Swarrot\Broker\Message;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Swarrot\Processor\InitializableInterface;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Processor\SleepyInterface;

class MaxExecutionTimeProcessor implements ConfigurableInterface, InitializableInterface, SleepyInterface
{
    /**
     * @var ProcessorInterface
     */
    protected $processor;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var float
     */
    protected $startTime;

    /**
     * @param ProcessorInterface $processor Processor
     * @param LoggerInterface    $logger    Logger
     */
    public function __construct(ProcessorInterface $processor, LoggerInterface $logger = null)
    {
        $this->processor = $processor;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(array(
                'max_execution_time' => 300,
            ))
            ->setAllowedTypes('max_execution_time', 'int')
        ;
    }

    /**
     * @param array $options
     */
    public function initialize(array $options)
    {
        $this->startTime = microtime(true);
    }

    /**
     * @param array $options
     *
     * @return bool
     */
    public function sleep(array $options)
    {
        return !$this->isTimeExceeded($options);
    }

    /**
     * {@inheritdoc}
     */
    public function process(Message $message, array $options)
    {
        if (true === $this->isTimeExceeded($options)) {
            return false;
        }

        return $this->processor->process($message, $options);
    }

    /**
     * isTimeExceeded.
     *
     * @param array $options
     *
     * @return bool
     */
    protected function isTimeExceeded(array $options)
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
