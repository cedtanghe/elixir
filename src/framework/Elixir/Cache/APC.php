<?php

namespace Elixir\Cache;

use Elixir\Cache\CacheAbstract;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class APC extends CacheAbstract
{
    /**
     * @see CacheAbstract::__construct()
     * @throws \RuntimeException
     */
    public function __construct($identifier) 
    {
        if (!(extension_loaded('apc') && ini_get('apc.enabled')))
        {
            throw new \RuntimeException('APC is not available.');
        }
        
        parent::__construct($identifier);
    }
    
    /**
     * @see CacheAbstract::exists()
     */
    public function exists($key)
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
                $result = $this->getEncoder()->decode($result);
            }
            
            return $result;
        }
        
        return is_callable($default) ? call_user_func($default) : $default;
    }
    
    /**
     * @see CacheAbstract::store()
     */
    public function store($key, $value, $ttl = self::DEFAULT_TTL)
    {
        if (null !== $this->encoder)
        {
            $value = $this->getEncoder()->encode($value);
        }
        
        return apc_store($this->identifier . $key, $value, $this->parseTimeToLive($ttl));
    }
    
    /**
     * @see CacheAbstract::delete()
     */
    public function delete($key)
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
