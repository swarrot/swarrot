<?php

namespace Swarrot\Processor\Callback;

use Swarrot\Broker\MessageInterface;
use Swarrot\Processor\ProcessorInterface;

class CallbackProcessor implements ProcessorInterface
{
    protected $process;

    public function __construct(\Closure $process)
    {
        $this->process = $process;
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, array $options)
    {
        call_user_func($this->process, $message, $options);
    }
}
