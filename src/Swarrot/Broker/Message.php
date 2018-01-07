<?php

namespace Swarrot\Broker;

class Message implements MessageInterface
{
    /**
     * @var string
     */
    protected $body;

    /**
     * Properties are similar to headers when using an \AMQPEnvelope object.
     *
     * @var array
     */
    protected $properties;

    /**
     * @var int
     */
    protected $id;

    /**
     * __construct.
     *
     * In AMQP 0.9.1, a message contains properties. One of this properties is
     * "headers".
     * In AMQP 1.0, a message contains both properties and headers.
     *
     * For example, RabbitMQ implement AMQP 0.9.1.
     * The "getHeaders" method of "\AMQPEnvelope" object actually return
     * message properties AND headers at the same level.
     * But if you want to have additional informations, you have to put it in
     * the "headers" property. All unknown properties will be deleted by the
     * broker.
     *
     * More information on AMQP version:
     *
     * @see: http://www.amqp.org/resources/download
     *
     * @param mixed $body
     * @param array $properties
     * @param mixed $id
     */
    public function __construct($body = null, array $properties = array(), $id = null)
    {
        $this->body = $body;
        $this->properties = $properties;
        $this->id = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function setBody($body)
    {
        return $this->body = $body;
    }

    /**
     * @deprecated getHeaders() method is deprecated. Use getProperties().
     * @return array
     */
    public function getHeaders()
    {
        trigger_error('getHeaders() method is deprecated. Use getProperties().', E_USER_DEPRECATED);

        return $this->getProperties();
    }

    /**
     * {@inheritdoc}
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param array $properties
     */
    public function setProperties(array $properties)
    {
        $this->properties = $properties;
    }

    /**
     * {@inheritdoc}
     */
    public function setProperty($name, $value)
    {
        $this->properties[$name] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getProperty($name, $default = null)
    {
        if(isset($this->properties[$name])) {

            return $this->properties[$name];
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->id = $id;
    }
}
