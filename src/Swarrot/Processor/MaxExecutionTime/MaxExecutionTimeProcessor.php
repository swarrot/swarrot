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
            ->setDefaults([
                'max_execution_time' => 300,
            ])
            ->setAllowedTypes('max_execution_time', 'int')
        ;
    }

    public function initialize(array $options)
    {
        $this->startTime = microtime(true);
    }

    /**
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
