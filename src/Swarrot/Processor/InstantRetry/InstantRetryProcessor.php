<?php

namespace Swarrot\Processor\InstantRetry;

use Swarrot\Broker\Message;
use Swarrot\Processor\InitializableInterface;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\DecoratorTrait;
use Swarrot\Processor\SleepyInterface;
use Swarrot\Processor\TerminableInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InstantRetryProcessor implements ProcessorInterface, ConfigurableInterface, InitializableInterface, SleepyInterface, TerminableInterface
{
    use DecoratorTrait {
        DecoratorTrait::setDefaultOptions as traitOptions;
    }

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
        $retry = 0;

        while ($retry++ < $options['instant_retry_attempts']) {
            try {
                return $this->processor->process($message, $options);
            } catch (\Throwable $e) {
                $this->handleException($e, $message, $options);
            } catch (\Exception $e) {
                $this->handleException($e, $message, $options);
            }
        }

        throw $e;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $this->traitOptions($resolver);

        $resolver->setDefaults(array(
            'instant_retry_delay' => 2000000,
            'instant_retry_attempts' => 3,
        ));
    }

    /**
     * @param \Exception|\Throwable $exception
     * @param Message               $message
     * @param array                 $options
     */
    private function handleException($exception, Message $message, array $options)
    {
        $this->logger and $this->logger->warning(
            sprintf(
                '[InstantRetry] An exception occurred. Message #%d will be processed again in %d ms',
                $message->getId(),
                $options['instant_retry_delay'] / 1000
            ),
            [
                'swarrot_processor' => 'instant_retry',
                'exception' => $exception,
            ]
        );

        usleep($options['instant_retry_delay']);
    }
}
