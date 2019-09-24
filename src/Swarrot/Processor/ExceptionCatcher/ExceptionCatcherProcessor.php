<?php

namespace Swarrot\Processor\ExceptionCatcher;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Swarrot\Broker\MessageInterface;
use Swarrot\Processor\ProcessorInterface;

class ExceptionCatcherProcessor implements ProcessorInterface
{
    /**
     * @var ProcessorInterface
     */
    protected $processor;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(ProcessorInterface $processor, LoggerInterface $logger = null)
    {
        $this->processor = $processor;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, array $options)
    {
        try {
            return $this->processor->process($message, $options);
        } catch (\Throwable $e) {
            $this->handleException($e, $message, $options);
        } catch (\Exception $e) {
            $this->handleException($e, $message, $options);
        }

        return;
    }

    /**
     * @param \Throwable|\Exception $exception
     * @param MessageInterface      $message
     * @param array                 $options
     */
    private function handleException($exception, MessageInterface $message, array $options)
    {
        $this->logger->error(
            '[ExceptionCatcher] An exception occurred. This exception has been caught.',
            [
                'swarrot_processor' => 'exception',
                'exception' => $exception,
            ]
        );
    }
}
