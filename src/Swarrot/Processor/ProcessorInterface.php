<?php

namespace Swarrot\Processor;

use Swarrot\AMQP\Message;

interface ProcessorInterface
{
    /**
     * __invoke
     *
     * @param Message $message The message given by a MessageProvider
     * @param array   $options An array containing all parameters
     *
     * @return boolean
     */
    public function __invoke(Message $message, array $options);
}
