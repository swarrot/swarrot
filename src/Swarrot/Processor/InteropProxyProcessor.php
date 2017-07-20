<?php

namespace Swarrot\Processor;

use Interop\Queue\PsrContext;
use Interop\Queue\PsrMessage;
use Interop\Queue\PsrProcessor;
use Swarrot\Broker\Message;

/**
 * This proxy class allows to use Swarrot processor with any queue-interop compatible consumer
 */
class InteropProxyProcessor implements PsrProcessor
{
    /**
     * @var ProcessorInterface
     */
    private $swarrotProcessor;

    /**
     * @var array
     */
    private $options;

    /**
     * @param ProcessorInterface $swarrotProcessor
     * @param array $options
     */
    public function __construct(ProcessorInterface $swarrotProcessor, array $options = array())
    {
        $this->swarrotProcessor = $swarrotProcessor;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function process(PsrMessage $message, PsrContext $context)
    {
        $properties = $message->getHeaders();
        $properties['headers'] = $message->getProperties();

        $this->swarrotProcessor->process(new Message($message->getBody(), $properties), $this->options);

        return self::ACK;
    }
}
