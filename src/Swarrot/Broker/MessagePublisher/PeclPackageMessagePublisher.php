<?php

namespace Swarrot\Broker\MessagePublisher;

use Swarrot\Broker\Message;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class PeclPackageMessagePublisher implements MessagePublisherInterface, PublishConfirmPublisherInterface
{
    protected $exchange;
    protected $flags;
    protected $logger;
    protected $confirmSelectMode = false;
    protected $timeout = 0;
    protected $lastDeliveryTag = 0;
    protected $pendingMessages = [];
    protected $ackHandler = null;
    protected $nackHandler = null;

    public function __construct(\AMQPExchange $exchange, $flags = AMQP_NOPARAM, LoggerInterface $logger = null)
    {
        $this->exchange = $exchange;
        $this->flags = $flags;
        $this->logger = $logger ?: new NullLogger();
    }

    private function getAckHandler()
    {
        if (!is_callable($this->ackHandler)) {
            $this->ackHandler = function ($deliveryTag, $multiple) {
                //remove acked from pending list
                if ($multiple) {
                    for ($tag = 0; $tag <= $multiple; $tag ++) {
                        unset($this->pendingMessages[$tag]);
                    }
                } else {
                    unset($this->pendingMessages[$deliveryTag]);
                }

                if (count($this->pendingMessages) > 0) {
                    return true; //still need to wait
                }
                return false;
            };
        }
        return $this->ackHandler;
    }

    private function getNackHandler()
    {
        if (!is_callable($this->ackHandler)) {
            $this->nackHandler = function ($deliveryTag, $multiple, $requeue) {
                throw new \Exception("Error publishing deliveryTag: " . $deliveryTag);
            };
        }
        return $this->nackHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function enterConfirmMode($timeout = 0)
    {
        if (!(method_exists($this->exchange->getChannel(), "confirmSelect"))) {
            throw new \Exception("Publisher confirms are not supported. Update your pecl amqp package");
        }
        if ($this->confirmSelectMode === false) {
            $this->exchange->getChannel()->confirmSelect();
            $this->exchange->getChannel()->setConfirmCallback($this->getAckHandler(), $this->getNackHandler());
            $this->confirmSelectMode = true;
        }
        $this->timeout = $timeout;
    }

    /**
     * {@inheritdoc}
     */
    public function publish(Message $message, $key = null)
    {
        $properties = $message->getProperties();
        if (isset($properties['application_headers'])) {
            if (!isset($properties['headers'])) {
                $properties['headers'] = [];
            }

            foreach ($properties['application_headers'] as $header => $value) {
                if (!is_array($value) || 2 !== count($value)) {
                    throw new \InvalidArgumentException(
                        'Unexpected value for application_headers "'.$header.'"'
                    );
                }

                $properties['headers'][$header] = $value[1];
            }

            unset($properties['application_headers']);
        }

        $body = $message->getBody();
        if (empty($body)) {
            $this->logger->notice('Publishing empty message.', [
                'message' => $body,
                'exchange' => $this->exchange->getName(),
                'key' => $key,
            ]);
        }

        $this->exchange->publish(
            $message->getBody(),
            $key,
            $this->flags,
            $this->sanitizeProperties($properties)
        );
        if ($this->confirmSelectMode) {
            //track published to see what needs to be acked
            $this->lastDeliveryTag += 1;
            $this->pendingMessages[$this->lastDeliveryTag] = $message;
            $this->exchange->getChannel()->waitForConfirm($this->timeout);
        }
    }

    private function sanitizeProperties(array $properties)
    {
        if (isset($properties['headers'])) {
            $properties['headers'] = array_filter($properties['headers'], function ($headerValue) {
                return !is_array($headerValue);
            });
        }

        // workaround for https://github.com/pdezwart/php-amqp/issues/170. See https://github.com/swarrot/swarrot/issues/103
        if (isset($properties['delivery_mode']) && 0 === $properties['delivery_mode']) {
            unset($properties['delivery_mode']);
        }

        return $properties;
    }

    /**
     * {@inheritdoc}
     */
    public function getExchangeName()
    {
        return $this->exchange->getName();
    }
}
