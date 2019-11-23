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
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'signal_handler_signals' => extension_loaded('pcntl') ? [SIGTERM, SIGINT, SIGQUIT] : [],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function sleep(array $options)
    {
        return !$this::$shouldExit;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Message $message, array $options)
    {
        $return = $this->processor->process($message, $options);

        if ($this::$shouldExit) {
            return false;
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        if (!extension_loaded('pcntl')) {
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
                SignalHandlerProcessor::$shouldExit = true;
            });
        }

        pcntl_async_signals(true);
    }
}
