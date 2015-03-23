<?php

namespace Elixir\Cache;

use Elixir\Cache\CacheAbstract;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Memcached extends CacheAbstract
{
    /**
     * @var \Memcached
     */
    protected $engine;

    /**
     * @see CacheAbstract::__construct()
     * @throws \RuntimeException
     */
    public function __construct($identifier) 
    {
        if (!class_exists('\Memcached')) 
        {
            throw new \RuntimeException('Memcached is not available.');
        }

        parent::__construct($identifier);
        $this->engine = new \Memcached($this->identifier);
    }

    public function __destruct() 
    {
        $this->engine = null;
    }

    /**
     * @see CacheAbstract::has()
     */
    public function has($key) 
    {
        if (!$this->engine->get($key)) 
        {
            return $this->engine->getResultCode() == \Memcached::RES_NOTFOUND;
        }

        return true;
    }

    /**
     * @see CacheAbstract::get()
     */
    public function get($key, $default = null) 
    {
        return $this->engine->get($key, $default);
    }

    /**
     * @see CacheAbstract::set()
     */
    public function set($key, $value, $TTL = 0)
    {
        if ($TTL != 0) 
        {
            $TTL = time() + $this->convertTTL($TTL);
        }

        if (null !== $this->encoder)
        {
            $value = $this->getEncoder()->encode($value);
        }

        $this->engine->set($key, $value, $TTL);
    }

    /**
     * @param string $key
     * @param integer $step
     * @return integer|null
     */
    public function incremente($key, $step = 1) 
    {
        $this->engine->increment($key, $step);
        return $this->get($key);
    }

    /**
     * @param string $key
     * @param integer $step
     * @return integer|null
     */
    public function decremente($key, $step = 1) 
    {
        $this->engine->decrement($key, $step);
        return $this->get($key);
    }

    /**
     * @see CacheAbstract::remove()
     */
    public function remove($key) 
    {
        $this->engine->delete($key);
    }

    /**
     * @see CacheAbstract::has()
     */
    public function clear()
    {
        $this->engine->flush();
    }

    /**
     * @param string $method
     * @param array $arguments
     */
    public function __call($method, $arguments) 
    {
        return call_user_func_array([$this->engine, $method], $arguments);
    }
}
