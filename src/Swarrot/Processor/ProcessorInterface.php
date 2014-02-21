<?php

namespace Swarrot\Processor;

use Swarrot\ParameterBag;
use Swarrot\AMQP\Message;

interface ProcessorInterface
{
    /**
     * __invoke
     *
     * @param Message      $message The message given by a MessageProvider
     * @param ParameterBag $bag     The bag containing all parameters
     *
     * @return boolean
     */
    public function __invoke(Message $message, ParameterBag $bag);
}
