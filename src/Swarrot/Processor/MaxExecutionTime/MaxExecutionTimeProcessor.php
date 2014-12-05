<?php

namespace Swarrot\Processor\MaxExecutionTime;

use Swarrot\Processor\ProcessorInterface;
use Swarrot\Broker\Message;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
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
     *
     * @param ProcessorInterface $processor Processor
     * @param LoggerInterface    $logger    Logger
     */
    public function __construct(ProcessorInterface $processor, LoggerInterface $logger = null)
    {
        $this->processor = $processor;
        $this->logger    = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'max_execution_time' => 300
        ));

        $resolver->setAllowedTypes(array(
            'max_execution_time' => 'integer',
        ));
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
     * @return boolean
     */
    public function sleep(array $options)
    {
        return !$this->isTimeExceeded($options);
    }

    /**
     * {@inheritDoc}
     */
    public function process(Message $message, array $options)
    {
        if (true === $this->isTimeExceeded($options)) {
            return false;
        }

        return $this->processor->process($message, $options);
    }

    /**
     * isTimeExceeded
     *
     * @param array $options
     *
     * @return boolean
     */
    protected function isTimeExceeded(array $options)
    {
        if (microtime(true) - $this->startTime > $options['max_execution_time']) {
            $this->logger and $this->logger->info(
                sprintf(
                    '[MaxExecutionTime] Max execution time have been reached (%d)',
                    $options['max_execution_time']
                ),
                array(
                    'swarrot_processor' => 'max_execution_time'
                )
            );

            return true;
        }

        return false;
    }
}
