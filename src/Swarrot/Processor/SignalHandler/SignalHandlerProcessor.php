<?php

namespace Swarrot\Processor\SignalHandler;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Swarrot\Broker\Message;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Processor\InitializableInterface;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\SleepyInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SignalHandlerProcessor implements ConfigurableInterface, SleepyInterface, InitializableInterface
{
    /**
     * @var bool
     */
    private static $shouldExit = false;

    private $processor;
    private $logger;

    public function __construct(ProcessorInterface $processor, LoggerInterface $logger = null)
    {
        $this->processor = $processor;
        $this->logger = $logger ?: new NullLogger();
    }

    public function setDefaultOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'signal_handler_signals' => \extension_loaded('pcntl') ? [\SIGTERM, \SIGINT, \SIGQUIT] : [],
        ]);
    }

    public function sleep(array $options): bool
    {
        return !$this::$shouldExit;
    }

    public function process(Message $message, array $options): bool
    {
        $return = $this->processor->process($message, $options);

        if ($this::$shouldExit) {
            return false;
        }

        return $return;
    }

    public function initialize(array $options): void
    {
        if (!\extension_loaded('pcntl')) {
            $this->logger->warning(
                '[SignalHandler] The SignalHandlerProcessor needs the pcntl extension to work',
                [
                    'swarrot_processor' => 'signal_handler',
                ]
            );

            return;
        }

        $signals = isset($options['signal_handler_signals']) ? $options['signal_handler_signals'] : [];
        foreach ($signals as $signal) {
            pcntl_signal($signal, function () {
                $this->logger->info(
                    '[SignalHandler] Signal received. Stop consumer now.',
                    [
                        'swarrot_processor' => 'signal_handler',
                    ]
                );
                self::$shouldExit = true;
            });
        }

        pcntl_async_signals(true);
    }
}
