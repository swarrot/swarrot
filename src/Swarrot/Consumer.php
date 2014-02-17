<?php

namespace Swarrot;

class Consumer
{
    protected $queue;

    public function __construct(\AMQPQueue $queue)
    {
        $this->queue = $queue;
    }

    /**
     * consume
     *
     * @param callable     $processor The processor to call
     * @param ParameterBag $bag       Parameters sent to the processor
     * @param int          $flags     Some Rabbit flags if needed
     *
     * @return void
     */
    public function consume($processor, ParameterBag $bag = null, $flags = AMQP_NOPARAM)
    {
        if (null === $bag) {
            $bag = new ParameterBag();
        }

        if ($processor instanceof InitializableInterface) {
            $processor->initialize($this->queue, $bag);
        }

        $continue = true;
        while ($continue) {
            $envelope = $this->queue->get($flags);

            if (!$envelope) {
                usleep($bag->get('poll_interval', 50000));
            } else {
                $continue = $processor($envelope, $this->queue, $bag);
            }
        }

        if ($processor instanceof TerminableInterface) {
            $processor->terminate($this->queue, $bag);
        }
    }

}
