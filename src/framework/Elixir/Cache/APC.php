<?php

namespace Elixir\Cache;

use Elixir\Cache\CacheAbstract;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class APC extends CacheAbstract
{
    /**
     * @var string 
     */
    protected $identifier;
    
    /**
     * @see CacheAbstract::__construct()
     * @throws \RuntimeException
     */
    public function __construct($identifier = '___CACHE_APC___') 
    {
        if (!(extension_loaded('apc') && ini_get('apc.enabled')))
        {
            throw new \RuntimeException('APC is not available.');
        }
        
        $this->identifier = preg_replace('/[^a-z0-9\-_]+/', '', strtolower($identifier));
    }
    
    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }
    
    /**
     * @see CacheAbstract::has()
     */
    public function has($key)
    {
        return apc_exists($this->identifier . $key);
    }
    
    /**
     * @see CacheAbstract::get()
     */
    public function get($key, $default = null)
    {
        $result = apc_fetch($this->identifier . $key, $success);
        
        if ($success)
        {
            if (null !== $this->encoder)
            {
                $result = $this->encoder->decode($result);
            }
            
            return $result;
        }
        
        return is_callable($default) ? call_user_func($default) : $default;
    }
    
    /**
     * @see CacheAbstract::set()
     */
    public function set($key, $value, $ttl = self::DEFAULT_TTL)
    {
        if (null !== $this->encoder)
        {
            $value = $this->encoder->encode($value);
        }
        
        return apc_store($this->identifier . $key, $value, $this->parseTimeToLive($ttl));
    }
    
    /**
     * @see CacheAbstract::remove()
     */
    public function remove($key)
    {
        return apc_delete($key);
    }
    
    /**
     * @see CacheAbstract::incremente()
     */
    public function incremente($key, $step = 1)
    {
        return apc_inc($this->identifier . $key, $step);
    }
    
    /**
     * @see CacheAbstract::decremente()
     */
    public function decremente($key, $step = 1)
    {
        return apc_dec($this->identifier . $key, $step);
    }
    
    /**
     * @see CacheAbstract::flush()
     */
    public function flush()
    {
        return apc_clear_cache('user');
    }
}
