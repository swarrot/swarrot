<?php

namespace Swarrot\Processor;

use Swarrot\Broker\Message;

interface ProcessorInterface
{
    /**
     * Process a message.
     * Return false to stop processing messages.
     *
     * @param Message              $message The message given by a MessageProvider
     * @param array<string, mixed> $options An array containing all parameters
     */
    public function process(Message $message, array $options): bool;
}
