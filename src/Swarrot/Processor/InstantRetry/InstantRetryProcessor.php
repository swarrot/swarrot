<?php

namespace Swarrot\Processor\InstantRetry;

use Swarrot\Broker\Message;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Processor\ProcessorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InstantRetryProcessor implements ConfigurableInterface
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
        $retry = 0;

        $processor = $this->processor;
        while ($retry++ < $options['instant_retry_attempts']) {
            try {
                $processor($message, $options);

                return;
            } catch (\Exception $e) {
                if (null !== $this->logger) {
                    $this->logger->warning(sprintf(
                        'An exception occured. Message #%d will be processed again in %d ms',
                        $message->getId(),
                        $options['instant_retry_delay']/1000
                    ));
                }

                usleep($options['instant_retry_delay']);
            }
        }

        throw $e;
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'instant_retry_delay' => 2000000,
            'instant_retry_attempts' => 3,
        ));
    }
}
