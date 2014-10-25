<?php

namespace Swarrot\Processor\Decorator\SignalHandler;

use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Processor\SleepyInterface;
use Psr\Log\LoggerInterface;
use Swarrot\Processor\Decorator\DecoratorInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SignalHandlerDecorator implements DecoratorInterface, ConfigurableInterface, SleepyInterface
{
    /**
     * @var boolean
     */
    protected static $shouldExit = false;


    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param LoggerInterface $logger Logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'signal_handler_signals' => array(SIGTERM, SIGINT, SIGQUIT)
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function sleep(array $options)
    {
        return !$this->shouldStop();
    }

    /**
     * {@inheritDoc}
     */
    public function decorate(ProcessorInterface $processor, Message $message, array $options)
    {
        if (!extension_loaded('pcntl')) {
            if (null !== $this->logger) {
                $this->logger->warning(
                    '[SignalHandler] The SignalHandlerProcessor needs the pcntl extension to work'
                );
            }

            return $processor->process($message, $options);
        }

        $signals = isset($options['signal_handler_signals']) ? $options['signal_handler_signals'] : array();
        foreach ($signals as $signal) {
            pcntl_signal($signal, function () {
                SignalHandlerDecorator::$shouldExit = true;
            });
        }

        $return = $processor->process($message, $options);

        if ($this->shouldStop()) {
            return false;
        }

        return $return;
    }

    /**
     * shouldStop
     *
     * @return boolean
     */
    protected function shouldStop()
    {
        pcntl_signal_dispatch();

        $signals = isset($options['signal_handler_signals']) ? $options['signal_handler_signals'] : array();
        foreach ($signals as $signal) {
            pcntl_signal($signal, SIG_DFL);
        }

        if ($this::$shouldExit) {
            if (null !== $this->logger) {
                $this->logger->info('[SignalHandler] Signal received. Stop consumer now.');
            }

            return true;
        }

        return false;
    }
}
