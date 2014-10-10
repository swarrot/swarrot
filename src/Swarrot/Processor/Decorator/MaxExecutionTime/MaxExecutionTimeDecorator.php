<?php

namespace Swarrot\Processor\Decorator\MaxExecutionTime;

use Swarrot\Processor\ProcessorInterface;
use Swarrot\Broker\Message;
use Psr\Log\LoggerInterface;
use Swarrot\Processor\Decorator\DecoratorInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Swarrot\Processor\InitializableInterface;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Processor\SleepyInterface;

class MaxExecutionTimeDecorator implements DecoratorInterface, ConfigurableInterface, InitializableInterface, SleepyInterface
{
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
     * @param LoggerInterface $logger Logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
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
    public function decorate(ProcessorInterface $processor, Message $message, array $options)
    {
        if (true === $this->isTimeExceeded($options)) {
            return false;
        }

        return $processor->process($message, $options);
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
            if (null !== $this->logger) {
                $this->logger->info(sprintf(
                    '[MaxExecutionTime] Max execution time have been reached (%d)',
                    $options['max_execution_time']
                ));
            }

            return true;
        }

        return false;
    }
}
