<?php

namespace Swarrot\Processor\Decorator;

use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;

interface DecoratorInterface
{
    /**
     * @param ProcessorInterface $processor
     * @param Message            $message
     * @param array              $options
     * @return bool
     */
    public function decorate(ProcessorInterface $processor, Message $message, array $options);
}
