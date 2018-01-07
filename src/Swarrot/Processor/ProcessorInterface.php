<?php

namespace Swarrot\Processor;

use Swarrot\Broker\MessageInterface;

interface ProcessorInterface
{
    /**
     * Process a message.
     * Return false to stop processing messages.
     *
     * @param MessageInterface $message The message given by a MessageProvider
     * @param array            $options An array containing all parameters
     *
     * @return bool|null
     */
    public function process(MessageInterface $message, array $options);
}
