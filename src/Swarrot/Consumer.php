<?php

namespace Swarrot;

use Swarrot\Broker\MessageProvider\MessageProviderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Processor\InitializableInterface;
use Swarrot\Processor\TerminableInterface;
use Swarrot\Processor\SleepyInterface;
use Psr\Log\LoggerInterface;

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
     * @param MessageProviderInterface $messageProvider
     * @param ProcessorInterface       $processor
     * @param OptionsResolver          $optionsResolver
     * @param LoggerInterface          $logger
     */
    public function __construct(MessageProviderInterface $messageProvider, ProcessorInterface $processor, OptionsResolver $optionsResolver = null, LoggerInterface $logger = null)
    {
        $this->messageProvider = $messageProvider;
        $this->processor = $processor;
        $this->optionsResolver = $optionsResolver ?: new OptionsResolver();
        $this->logger = $logger;
    }

    /**
     * consume.
     *
     * @param array $options Parameters sent to the processor
     */
    public function consume(array $options = array())
    {
        if (null !== $this->logger) {
            $this->logger->debug(sprintf(
                'Start consuming queue %s.',
                $this->messageProvider->getQueueName()
            ));
        }
        $this->optionsResolver->setDefaults(array(
            'poll_interval' => 50000,
            'queue' => $this->messageProvider->getQueueName(),
        ));

        if ($this->processor instanceof ConfigurableInterface) {
            $this->processor->setDefaultOptions($this->optionsResolver);
        }

        $options = $this->optionsResolver->resolve($options);

        if ($this->processor instanceof InitializableInterface) {
            $this->processor->initialize($options);
        }

        while (true) {
            while (null !== $message = $this->messageProvider->get()) {
                if (false === $this->processor->process($message, $options)) {
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
     * @param OptionsResolver $optionsResolver
     *
     * @return self
     */
    public function setOptionsResolver(OptionsResolver $optionsResolver)
    {
        $this->optionsResolver = $optionsResolver;

        return $this;
    }
}
