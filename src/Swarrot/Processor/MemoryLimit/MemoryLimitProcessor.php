<?php

namespace Swarrot\Processor\MemoryLimit;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Swarrot\Broker\Message;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Processor\ProcessorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemoryLimitProcessor implements ConfigurableInterface
{
    private $processor;
    private $logger;

    public function __construct(ProcessorInterface $processor, LoggerInterface $logger = null)
    {
        $this->processor = $processor;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function process(Message $message, array $options): bool
    {
        $return = $this->processor->process($message, $options);

        $memoryUsage = memory_get_usage();
        if (null !== $options['memory_limit'] && $memoryUsage >= $options['memory_limit'] * 1024 * 1024) {
            $this->logger->info(
                '[MemoryLimit] Memory limit has been reached',
                [
                    'memory_limit' => $options['memory_limit'],
                    'memory_usage' => $memoryUsage,
                    'swarrot_processor' => 'memory_limit',
                ]
            );

            return false;
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'memory_limit' => null,
        ]);

        $resolver->setAllowedTypes('memory_limit', ['integer', 'null']);
    }
}
