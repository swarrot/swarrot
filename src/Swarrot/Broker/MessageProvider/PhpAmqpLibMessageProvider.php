<?php

namespace Swarrot\Broker\MessageProvider;

use PhpAmqpLib\Channel\AMQPChannel;
use Swarrot\Broker\Message;

class PhpAmqpLibMessageProvider implements MessageProviderInterface
{
    /**
     * @var AMQPChannel
     */
    private $channel;

    /**
     * @var string
     */
    private $queueName;

    /**
     * @param AMQPChannel $channel
     * @param string      $queueName
     */
    public function __construct(AMQPChannel $channel, $queueName)
    {
        $this->channel   = $channel;
        $this->queueName = $queueName;
    }

    /**
     * {@inheritDoc}
     */
    public function get()
    {
        $envelope = $this->channel->basic_get($this->queueName);

        if (null === $envelope) {
            return null;
        }

        // Explanation on these properties can be found at:
        // https://github.com/videlalvaro/php-amqplib/blob/091/doc/AMQPMessage.md
        $properties = array(
            // Here are all the supported message properties by the php-amqplib library
            'content_type'     => $envelope->has('content_type')        ? $envelope->get('content_type')        : '',
            'delivery_mode'    => $envelope->has('delivery_mode')       ? $envelope->get('delivery_mode')       : 0,
            'content_encoding' => $envelope->has('content_encoding')    ? $envelope->get('content_encoding')    : '',
            'type'             => $envelope->has('type')                ? $envelope->get('type')                : '',
            'timestamp'        => $envelope->has('timestamp')           ? $envelope->get('timestamp')           : 0,
            'priority'         => $envelope->has('priority')            ? $envelope->get('priority')            : 0,
            'expiration'       => $envelope->has('expiration')          ? $envelope->get('expiration')          : '',
            'app_id'           => $envelope->has('app_id')              ? $envelope->get('app_id')              : '',
            'message_id'       => $envelope->has('message_id')          ? $envelope->get('message_id')          : '',
            'reply_to'         => $envelope->has('reply_to')            ? $envelope->get('reply_to')            : '',
            'correlation_id'   => $envelope->has('correlation_id')      ? $envelope->get('correlation_id')      : '',
            'user_id'          => $envelope->has('user_id')             ? $envelope->get('user_id')             : 0,
            'cluster_id'       => $envelope->has('cluster_id')          ? $envelope->get('cluster_id')          : 0,

            // Following parameters are delivery information added by the php-amqplib library
            'channel'       => isset($envelope->delivery_info['channel'])      ? $envelope->delivery_info['channel']      : '',
            'consumer_tag'  => isset($envelope->delivery_info['consumer_tag']) ? $envelope->delivery_info['consumer_tag'] : '',
            'delivery_tag'  => isset($envelope->delivery_info['delivery_tag']) ? $envelope->delivery_info['delivery_tag'] : '',
            'is_redelivery' => isset($envelope->delivery_info['redelivered'])  ? $envelope->delivery_info['redelivered']  : false,
            'exchange_name' => isset($envelope->delivery_info['exchange'])     ? $envelope->delivery_info['exchange']     : '',
            'routing_key'   => isset($envelope->delivery_info['routing_key'])  ? $envelope->delivery_info['routing_key']  : ''
        );

        $properties['headers'] = array();
        if ($envelope->has('application_headers')) {
            foreach ($envelope->get('application_headers') as $key => $value) {
                $properties['headers'][$key] = $value[1];
            }
        }

        return new Message($envelope->body, $properties, $envelope->get('delivery_tag'));
    }

    /**
     * {@inheritDoc}
     */
    public function ack(Message $message)
    {
        $this->channel->basic_ack($message->getId());
    }

    /**
     * {@inheritDoc}
     */
    public function nack(Message $message, $requeue = false)
    {
        $this->channel->basic_nack($message->getId(), false, $requeue);
    }

    /**
     * {@inheritDoc}
     */
    public function getQueueName()
    {
        return $this->queueName;
    }
}
