<?php

namespace Swarrot\Processor\Callback;

use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;

class CallbackProcessor implements ProcessorInterface
{
    private $process;

    public function __construct(callable $process)
    {
        $this->process = $process;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Message $message, array $options): bool
    {
        return \call_user_func($this->process, $message, $options);
    }
}
