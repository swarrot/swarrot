<?php

namespace Swarrot\Processor\MaxExecutionTime;

use Swarrot\Processor\ProcessorInterface;
use Swarrot\Broker\Message;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Swarrot\Processor\InitializableInterface;
use Swarrot\Processor\ConfigurableInterface;

class MaxExecutionTimeProcessor implements ConfigurableInterface, InitializableInterface
{
    protected $processor;
    protected $logger;
    protected $startTime;

    public function __construct(ProcessorInterface $processor, LoggerInterface $logger = null)
    {
        $this->processor       = $processor;
        $this->logger          = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(Message $message, array $options)
    {
        $processor = $this->processor;
        if (true === $this->isTimeExceeded($options)) {
            return false;
        }

        return $processor($message, $options);
    }

    protected function isTimeExceeded(array $options)
    {
        if (microtime(true) - $this->startTime > $options['max_execution_time']) {
            if (null !== $this->logger) {
                $this->logger->debug(sprintf(
                    'Max execution time have been reached (%d)',
                    $options['max_execution_time']
                ));
            }

            return true;
        }

        return false;
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
     * {@inheritDoc}
     */
    public function initialize(array $options)
    {
        $this->startTime = microtime(true);
    }

    /**
     * {@inheritDoc}
     */

    public function sleep(array $options)
    {
        return !$this->isTimeExceeded($options);
    }
}
