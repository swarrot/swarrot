<?php

namespace Swarrot\Processor\ExceptionCatcher;

use Swarrot\Broker\Message;
use Swarrot\Processor\InitializableInterface;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\DecoratorTrait;
use Swarrot\Processor\SleepyInterface;
use Swarrot\Processor\TerminableInterface;
use Psr\Log\LoggerInterface;

class ExceptionCatcherProcessor implements ProcessorInterface, ConfigurableInterface, InitializableInterface, SleepyInterface, TerminableInterface
{
    use DecoratorTrait;

    /**
     * @var LoggerInterface
     */
    protected $logger;

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
     * @param Throwable|Exception  $exception
     * @param Message              $message
     * @param array                $options
     */
    private function handleException($exception, Message $message, array $options)
    {
        $this->logger and $this->logger->error(
            '[ExceptionCatcher] An exception occurred. This exception has been caught.',
            [
                'swarrot_processor' => 'exception',
                'exception' => $exception,
            ]
        );
    }
}
