<?php

namespace Swarrot\Processor\ExceptionCatcher;

use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ExceptionCatcherProcessor implements ProcessorInterface
{
    protected $processor;
    protected $logger;

    public function __construct(ProcessorInterface $processor, LoggerInterface $logger = null)
    {
        $this->processor = $processor;
        $this->logger    = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(Message $message, array $options)
    {
        $processor = $this->processor;
        try {
            $processor($message, $options);
        } catch (\Exception $e) {
            if (null !== $this->logger) {
                $this->logger->warning(sprintf(
                    'An exception occurred. This exception have been catch. Exception message: %s',
                    $e->getMessage()
                ));
            }
        }

        return;
    }
}
