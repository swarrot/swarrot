<?php

namespace Swarrot\Processor\SignalHandler;

use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\InitializableInterface;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Processor\SleepyInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SignalHandlerProcessor implements InitializableInterface, ConfigurableInterface, SleepyInterface
{
    static protected $shouldExit = false;

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
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'signal_handler_signals' => array(SIGTERM, SIGINT, SIGQUIT)
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function initialize(array $options)
    {
        if (!extension_loaded('pcntl')) {
            return;
        }

        $signals = isset($options['signal_handler_signals']) ? $options['signal_handler_signals'] : array();
        foreach ($signals as $signal) {
            pcntl_signal($signal, function () {
                SignalHandlerProcessor::$shouldExit = true;
            });
        }
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
    public function process(Message $message, array $options)
    {
        $return = $this->processor->process($message, $options);

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
        if (!extension_loaded('pcntl')) {
            return false;
        }

        pcntl_signal_dispatch();

        $signals = isset($options['signal_handler_signals']) ? $options['signal_handler_signals'] : array();
        foreach ($signals as $signal) {
            pcntl_signal($signal, SIG_DFL);
        }

        if ($this::$shouldExit) {
            $this->logger->info('[SignalHandler] Signal received. Stop consumer now.');

            return true;
        }

        return false;
    }
}
