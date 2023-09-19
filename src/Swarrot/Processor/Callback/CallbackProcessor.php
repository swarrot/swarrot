<?php

namespace Swarrot\Processor\Callback;

use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;

/**
 * @final since 4.16.0
 */
class CallbackProcessor implements ProcessorInterface
{
    private $process;

    public function __construct(callable $process)
    {
        $this->process = $process;
    }

    public function process(Message $message, array $options): bool
    {
        return \call_user_func($this->process, $message, $options);
    }
}
