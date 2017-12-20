<?php

namespace Swarrot\Processor\ExceptionCatcher;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Swarrot\Broker\Message;
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
    public function process(Message $message, array $options)
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
     * @param Message               $message
     * @param array                 $options
     */
    private function handleException($exception, Message $message, array $options)
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
