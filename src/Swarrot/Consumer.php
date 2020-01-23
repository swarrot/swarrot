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

class Consumer
{
    /**
     * @var MessageProviderInterface
     */
    protected $messageProvider;

    /**
     * @var ProcessorInterface
     */
    protected $processor;

    /**
     * @var OptionsResolver
     */
    protected $optionsResolver;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param OptionsResolver $optionsResolver
     * @param LoggerInterface $logger
     */
    public function __construct(MessageProviderInterface $messageProvider, ProcessorInterface $processor, OptionsResolver $optionsResolver = null, LoggerInterface $logger = null)
    {
        $this->messageProvider = $messageProvider;
        $this->processor = $processor;
        $this->optionsResolver = $optionsResolver ?: new OptionsResolver();
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * consume.
     *
     * @param array $options Parameters sent to the processor
     */
    public function consume(array $options = [])
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
                    @trigger_error(sprintf('Processors must return a bool since Swarrot 3.7', __CLASS__), E_USER_DEPRECATED);
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

    /**
     * @return MessageProviderInterface
     */
    public function getMessageProvider()
    {
        return $this->messageProvider;
    }

    /**
     * @param MessageProviderInterface $messageProvider Message provider
     *
     * @return self
     */
    public function setMessageProvider(MessageProviderInterface $messageProvider)
    {
        $this->messageProvider = $messageProvider;

        return $this;
    }

    /**
     * @return ProcessorInterface
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * @param ProcessorInterface $processor
     *
     * @return self
     */
    public function setProcessor($processor)
    {
        $this->processor = $processor;

        return $this;
    }

    /**
     * @return OptionsResolver
     */
    public function getOptionsResolver()
    {
        return $this->optionsResolver;
    }

    /**
     * @return self
     */
    public function setOptionsResolver(OptionsResolver $optionsResolver)
    {
        $this->optionsResolver = $optionsResolver;

        return $this;
    }
}
