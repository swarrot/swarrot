<?php

namespace Swarrot;

use Swarrot\AMQP\MessageProviderInterface;
use Swarrot\AMQP\Message;
use Swarrot\ParameterBag;

class Consumer
{
    protected $messageProvider;

    public function __construct(MessageProviderInterface $messageProvider)
    {
        $this->messageProvider = $messageProvider;
    }

    /**
     * consume
     *
     * @param callable     $processor The processor to call
     * @param ParameterBag $bag       Parameters sent to the processor
     *
     * @return void
     */
    public function consume($processor, ParameterBag $bag = null)
    {
        if (null === $bag) {
            $bag = new ParameterBag();
        }

        if ($processor instanceof InitializableInterface) {
            $processor->initialize($bag);
        }

        $continue = true;
        while ($continue) {
            $message = $this->messageProvider->get();

            if (null !== $message) {
                $continue = $processor($message, $bag);
            } else {
                usleep($bag->get('poll_interval', 50000));
            }
        }

        if ($processor instanceof TerminableInterface) {
            $processor->terminate($bag);
        }
    }

}
