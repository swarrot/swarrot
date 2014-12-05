<?php

namespace Swarrot\Processor\InstantRetry;

use Swarrot\Broker\Message;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Processor\ProcessorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InstantRetryProcessor implements ConfigurableInterface
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
        $this->logger    = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function process(Message $message, array $options)
    {
        $retry = 0;

        while ($retry++ < $options['instant_retry_attempts']) {
            try {
                return $this->processor->process($message, $options);
            } catch (\Exception $e) {
                $this->logger and $this->logger->warning(
                    sprintf(
                        '[InstantRetry] An exception occurred. Message #%d will be processed again in %d ms',
                        $message->getId(),
                        $options['instant_retry_delay']/1000
                    ),
                    array(
                        'swarrot_processor' => 'instant_retry',
                        'exception'         => $e,
                    )
                );

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
