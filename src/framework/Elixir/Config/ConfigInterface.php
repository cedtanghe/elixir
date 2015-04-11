<?php

namespace Elixir\Config;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
interface ConfigInterface 
{
    /**
     * @param mixed $key
     * @return boolean
     */
    public function has($key);

    /**
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * @param mixed $key
     * @param mixed $value
     */
    public function set($key, $value);

    /**
     * @param mixed $key
     */
    public function remove($key);

    /**
     * @return array
     */
    public function gets();

    /**
     * @param array $data
     */
    public function sets(array $data);

    /**
     * @param ConfigInterface|array
     * @param boolean $recursive
     */
    public function merge($data, $recursive = false);
}
