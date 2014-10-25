<?php

namespace Swarrot\Processor\Decorator\InstantRetry;

use Swarrot\Broker\Message;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Processor\ProcessorInterface;
use Psr\Log\LoggerInterface;
use Swarrot\Processor\Decorator\DecoratorInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InstantRetryDecorator implements DecoratorInterface, ConfigurableInterface
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
        $retry = 0;

        while ($retry++ < $options['instant_retry_attempts']) {
            try {
                return $processor->process($message, $options);
            } catch (\Exception $e) {
                if (null !== $this->logger) {
                    $this->logger->warning(
                        sprintf(
                            '[InstantRetry] An exception occurred. Message #%d will be processed again in %d ms',
                            $message->getId(),
                            $options['instant_retry_delay']/1000
                        ),
                        array(
                            'exception' => $e,
                        )
                    );
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
