<?php

namespace Swarrot\Processor\MaxExecutionTime;

use Swarrot\Processor\ProcessorInterface;
use Swarrot\Broker\Message;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Swarrot\Processor\InitializableInterface;
use Swarrot\Processor\DecoratorTrait;
use Swarrot\Processor\SleepyInterface;
use Swarrot\Processor\TerminableInterface;

class MaxExecutionTimeProcessor implements ProcessorInterface, ConfigurableInterface, InitializableInterface, SleepyInterface, TerminableInterface
{
    use DecoratorTrait {
        DecoratorTrait::setDefaultOptions as decoratorOptions;
        DecoratorTrait::initialize as decoratorInitializer;
        DecoratorTrait::sleep as decoratorSleep;
    }

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
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $this->decoratorOptions($resolver);

        $resolver->setDefaults(array(
            'max_execution_time' => 300,
        ));

        if (method_exists($resolver, 'setDefined')) {
            $resolver->setAllowedTypes('max_execution_time', 'int');
        } else {
            // BC for OptionsResolver < 2.6
            $resolver->setAllowedTypes(array(
                'max_execution_time' => 'int',
            ));
        }
    }

    /**
     * @param array $options
     */
    public function initialize(array $options)
    {
        $this->decoratorInitializer($options);

        $this->startTime = microtime(true);
    }

    /**
     * @param array $options
     *
     * @return bool
     */
    public function sleep(array $options)
    {
        return !$this->isTimeExceeded($options) && $this->decoratorSleep($options);
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
            $this->logger and $this->logger->info(
                sprintf(
                    '[MaxExecutionTime] Max execution time have been reached (%d)',
                    $options['max_execution_time']
                ),
                [
                    'swarrot_processor' => 'max_execution_time',
                ]
            );

            return true;
        }

        return false;
    }
}
