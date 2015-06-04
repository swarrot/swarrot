<?php

namespace Swarrot\Processor;

use Swarrot\Broker\Message;

interface ProcessorInterface
{
    /**
     * process.
     *
     * @param Message $message The message given by a MessageProvider
     * @param array   $options An array containing all parameters
     *
     * @return bool
     */
    public function process(Message $message, array $options);
}
