<?php

namespace Swarrot\Broker;

interface MessageProviderInterface
{
    /**
     * get
     *
     * @return void
     */
    public function get();

    /**
     * ack
     *
     * @param Message $message
     *
     * @return void
     */
    public function ack(Message $message);

    /**
     * nack
     *
     * @param Message $message
     *
     * @return void
     */
    public function nack(Message $message);
}
