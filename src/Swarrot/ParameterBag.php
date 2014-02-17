<?php

namespace Swarrot;

/**
 * @author Olivier Dolbeau <odolbeau@gmail.com>
 */
class ParameterBag
{
    private $parameters;

    /**
     * get
     *
     * @param string $parameter
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($parameter, $default = null)
    {
        if (!$this->has($parameter)) {
            return $default;
        }

        return $this->parameters[$parameter];
    }

    /**
     * has
     *
     * @param string $parameter
     *
     * @return boolean
     */
    public function has($parameter)
    {
        return isset($this->parameters[$parameter]);
    }

    /**
     * set
     *
     * @param string $parameter
     * @param mixed  $value
     *
     * @return ParameterBag
     */
    public function set($parameter, $value)
    {
        $this->parameters[$parameter] = $value;

        return $this;
    }
}

