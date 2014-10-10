<?php

namespace Swarrot\Processor\Decorator\ExceptionCatcher;

use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;
use Psr\Log\LoggerInterface;
use Swarrot\Processor\Decorator\DecoratorInterface;

class ExceptionCatcherDecorator implements DecoratorInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function decorate(ProcessorInterface $processor, Message $message, array $options)
    {
        try {
            return $processor->process($message, $options);
        } catch (\Exception $e) {
            if (null !== $this->logger) {
                $this->logger->error(
                    '[ExceptionCatcher] An exception occurred. This exception have been catch.',
                    array(
                        'exception' => $e,
                    )
                );
            }
        }
    }
}
