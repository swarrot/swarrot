<?php

namespace Swarrot\Processor\MemoryLimit;

use Psr\Log\LoggerInterface;
use Swarrot\Broker\Message;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Processor\ProcessorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemoryLimitProcessor implements ConfigurableInterface
{
    /**
     * @var ProcessorInterface
     */
    private $processor;

    /**
     * @var LoggerInterface
     */
    private $logger;

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
    public function process(Message $message, array $options)
    {
        $return = $this->processor->process($message, $options);

        if (null !== $options['memory_limit'] && memory_get_usage() >= $options['memory_limit'] * 1024 * 1024) {
            $this->logger and $this->logger->info(
                sprintf('[MemoryLimit] Memory limit has been reached (%d MB)', $options['memory_limit']),
                [
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
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'memory_limit' => null,
        ));

        $resolver->setAllowedTypes(array(
            'memory_limit' => array('integer', 'null'),
        ));
    }
}
