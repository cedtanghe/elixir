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
                $result = $this->getEncoder()->decode($result);
            }
            
            return $result;
        }
        
        return is_callable($default) ? call_user_func($default) : $default;
    }
    
    /**
     * @see CacheAbstract::set()
     */
    public function set($key, $value, $TTL = 0)
    {
        $TTL = $this->convertTTL($TTL);
        
        if (null !== $this->encoder)
        {
            $value = $this->getEncoder()->encode($value);
        }
        
        apc_store($this->identifier . $key, $value, $TTL);
    }
    
    /**
     * @param string $key
     * @param integer $step
     * @return integer|null
     */
    public function incremente($key, $step = 1)
    {
        apc_inc($this->identifier . $key, $step);
        return $this->get($pKey);
    }
    
    /**
     * @param string $key
     * @param integer $step
     * @return integer|null
     */
    public function decremente($key, $step = 1)
    {
        apc_dec($this->identifier . $key, $step);
        return $this->get($pKey);
    }

    /**
     * @see CacheAbstract::remove()
     */
    public function remove($key)
    {
        apc_delete($key);
    }
    
    /**
     * @see CacheAbstract::has()
     */
    public function clear()
    {
        apc_clear_cache('user');
    }
}
