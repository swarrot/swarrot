<?php

namespace Swarrot;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Swarrot\Broker\MessageProvider\MessageProviderInterface;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Processor\InitializableInterface;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\SleepyInterface;
use Swarrot\Processor\TerminableInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @final since 4.16.0
 */
class Consumer
{
    private MessageProviderInterface $messageProvider;
    private ProcessorInterface $processor;
    private OptionsResolver $optionsResolver;
    private LoggerInterface $logger;

    public function __construct(MessageProviderInterface $messageProvider, ProcessorInterface $processor, ?OptionsResolver $optionsResolver = null, ?LoggerInterface $logger = null)
    {
        $this->messageProvider = $messageProvider;
        $this->processor = $processor;
        $this->optionsResolver = $optionsResolver ?: new OptionsResolver();
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * @param array<string, mixed> $options Parameters sent to the processor
     */
    public function consume(array $options = []): void
    {
        $queueName = $this->messageProvider->getQueueName();

        $this->logger->debug('Start consuming queue.', [
            'queue' => $queueName,
        ]);

        $this->optionsResolver->setDefaults([
            'poll_interval' => 50000,
            'queue' => $queueName,
        ]);

        if ($this->processor instanceof ConfigurableInterface) {
            $this->processor->setDefaultOptions($this->optionsResolver);
        }

        $options = $this->optionsResolver->resolve($options);

        if ($this->processor instanceof InitializableInterface) {
            $this->processor->initialize($options);
        }

        while (true) {
            while (null !== $message = $this->messageProvider->get()) {
                $result = $this->processor->process($message, $options);
                if (!\is_bool($result)) {
                    @trigger_error('Processors must return a bool since Swarrot 3.7', \E_USER_DEPRECATED);
                }
                if (false === $result) {
                    break 2;
                }
            }

            if ($this->processor instanceof SleepyInterface) {
                if (false === $this->processor->sleep($options)) {
                    break;
                }
            }

            usleep($options['poll_interval']);
        }

        if ($this->processor instanceof TerminableInterface) {
            $this->processor->terminate($options);
        }
    }

    public function getMessageProvider(): MessageProviderInterface
    {
        return $this->messageProvider;
    }

    public function setMessageProvider(MessageProviderInterface $messageProvider): self
    {
        $this->messageProvider = $messageProvider;

        return $this;
    }

    public function getProcessor(): ProcessorInterface
    {
        return $this->processor;
    }

    public function setProcessor(ProcessorInterface $processor): self
    {
        $this->processor = $processor;

        return $this;
    }

    public function getOptionsResolver(): OptionsResolver
    {
        return $this->optionsResolver;
    }

    public function setOptionsResolver(OptionsResolver $optionsResolver): self
    {
        $this->optionsResolver = $optionsResolver;

        return $this;
    }
}
