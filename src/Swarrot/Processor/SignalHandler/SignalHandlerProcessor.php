<?php

namespace Swarrot\Processor\SignalHandler;

use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Processor\SleepyInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SignalHandlerProcessor implements ConfigurableInterface, SleepyInterface
{
    /**
     * @var bool
     */
    protected static $shouldExit = false;

    /**
     * @var ProcessorInterface
     */
    protected $processor;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param ProcessorInterface $processor Processor
     * @param LoggerInterface    $logger    Logger
     */
    public function __construct(ProcessorInterface $processor, LoggerInterface $logger = null)
    {
        $this->processor = $processor;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'signal_handler_signals' => extension_loaded('pcntl') ? [SIGTERM, SIGINT, SIGQUIT] : [],
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function sleep(array $options)
    {
        if (!extension_loaded('pcntl')) {
            return true;
        }

        return !$this->shouldStop();
    }

    /**
     * {@inheritdoc}
     */
    public function process(Message $message, array $options)
    {
        if (!extension_loaded('pcntl')) {
            $this->logger and $this->logger->warning(
                '[SignalHandler] The SignalHandlerProcessor needs the pcntl extension to work',
                [
                    'swarrot_processor' => 'signal_handler',
                ]
            );

            return $this->processor->process($message, $options);
        }

        $signals = isset($options['signal_handler_signals']) ? $options['signal_handler_signals'] : array();
        foreach ($signals as $signal) {
            pcntl_signal($signal, function () {
                SignalHandlerProcessor::$shouldExit = true;
            });
        }

        $return = $this->processor->process($message, $options);

        if ($this->shouldStop()) {
            return false;
        }

        return $return;
    }

    /**
     * shouldStop.
     *
     * @return bool
     */
    protected function shouldStop()
    {
        pcntl_signal_dispatch();

        $signals = isset($options['signal_handler_signals']) ? $options['signal_handler_signals'] : array();
        foreach ($signals as $signal) {
            pcntl_signal($signal, SIG_DFL);
        }

        if ($this::$shouldExit) {
            $this->logger and $this->logger->info(
                '[SignalHandler] Signal received. Stop consumer now.',
                [
                    'swarrot_processor' => 'signal_handler',
                ]
            );

            return true;
        }

        return false;
    }
}
