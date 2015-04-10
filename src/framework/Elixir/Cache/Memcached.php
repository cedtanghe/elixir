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
     * @var string 
     */
    protected $identifier;

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

        $this->identifier = preg_replace('/[^a-z0-9\-_]+/i', '', strtolower($identifier));
        $this->engine = new \Memcached($this->identifier);
    }

    /**
     * @ignore
     */
    public function __destruct() 
    {
        $this->engine = null;
    }
    
    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @see CacheAbstract::exists()
     */
    public function exists($key) 
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
        $value = $this->engine->get($key, $default);
        
        if (null !== $this->encoder)
        {
            $value = $this->encoder->encode($value);
        }
        
        return $value;
    }

    /**
     * @see CacheAbstract::set()
     */
    public function store($key, $value, $ttl = self::DEFAULT_TTL)
    {
        if ($ttl != 0) 
        {
            $ttl = time() + $this->parseTimeToLive($ttl);
        }

        if (null !== $this->encoder)
        {
            $value = $this->encoder->encode($value);
        }

        return $this->engine->set($key, $value, $ttl);
    }
    
    /**
     * @see CacheAbstract::delete()
     */
    public function delete($key) 
    {
        return $this->engine->delete($key);
    }

    /**
     * @see CacheAbstract::incremente()
     */
    public function incremente($key, $step = 1) 
    {
        return $this->engine->increment($key, $step);
    }

    /**
     * @see CacheAbstract::decremente()
     */
    public function decremente($key, $step = 1) 
    {
        return $this->engine->decrement($key, $step);
    }
    
    /**
     * @see CacheAbstract::flush()
     */
    public function flush()
    {
        return $this->engine->flush();
    }

    /**
     * @ignore
     */
    public function __call($method, $arguments) 
    {
        return call_user_func_array([$this->engine, $method], $arguments);
    }
}
