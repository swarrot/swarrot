<?php

namespace Swarrot\Broker;

interface MessageInterface
{
    /**
     * @return null|string
     */
    public function getBody();

    /**
     * @param string $body
     */
    public function setBody($body);

    /**
     * @return array
     */
    public function getProperties();

    /**
     * @param array $properties
     */
    public function setProperties(array $properties);

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function setProperty($name, $value);

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getProperty($name, $default = null);

    /**
     * @return mixed
     */
    public function getId();

    /**
     * @param mixed $id
     */
    public function setId($id);
}
