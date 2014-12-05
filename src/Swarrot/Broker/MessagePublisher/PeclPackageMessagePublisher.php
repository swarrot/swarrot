<?php

namespace Swarrot\Broker\MessagePublisher;

use Swarrot\Broker\Message;

class PeclPackageMessagePublisher implements MessagePublisherInterface
{
    protected $exchange;
    protected $flags;

    public function __construct(\AMQPExchange $exchange, $flags = AMQP_NOPARAM)
    {
        $this->exchange = $exchange;
        $this->flags    = $flags;
    }

    /**
     * {@inheritDoc}
     */
    public function publish(Message $message, $key = null)
    {
        $properties = $message->getProperties();
        if (isset($properties['application_headers'])) {
            if (!isset($properties['headers'])) {
                $properties['headers'] = array();
            }

            foreach ($properties['application_headers'] as $header => $value) {
                if (!is_array($value) || 2 !== count($value)) {
                    throw new \InvalidArgumentException(
                        'Unexpected value for application_headers "' . $header . '"'
                    );
                }

                $properties['headers'][$header] = $value[1];
            }

            unset($properties['application_headers']);
        }

        $this->exchange->publish(
            $message->getBody(),
            $key,
            $this->flags,
            $properties
        );
    }
}
