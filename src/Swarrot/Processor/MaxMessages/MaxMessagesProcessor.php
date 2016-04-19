<?php

namespace Swarrot\Processor\MaxMessages;

use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\InitializableInterface;
use Swarrot\Processor\DecoratorTrait;
use Swarrot\Processor\SleepyInterface;
use Swarrot\Processor\TerminableInterface;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Broker\Message;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MaxMessagesProcessor implements ProcessorInterface, ConfigurableInterface, InitializableInterface, SleepyInterface, TerminableInterface
{
    use DecoratorTrait {
        DecoratorTrait::setDefaultOptions as decoratorOptions;
    }

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var int
     */
    protected $messagesProcessed = 0;

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

        if (++$this->messagesProcessed >= $options['max_messages']) {
            $this->logger and $this->logger->info(
                sprintf('[MaxMessages] Max messages have been reached (%d)', $options['max_messages']),
                [
                    'swarrot_processor' => 'max_messages',
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
        $this->decoratorOptions($resolver);

        $resolver->setDefaults(array(
            'max_messages' => 100,
        ));

        if (method_exists($resolver, 'setDefined')) {
            $resolver->setAllowedTypes('max_messages', 'int');
        } else {
            // BC for OptionsResolver < 2.6
            $resolver->setAllowedTypes(array(
                'max_messages' => 'int',
            ));
        }
    }
}
