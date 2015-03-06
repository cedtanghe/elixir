<?php

namespace Elixir\Cache;

use Elixir\Cache\CacheAbstract;
use Elixir\Util\Arr as ArrUtils;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Arr extends CacheAbstract
{
    /**
     * @var array|\ArrayAccess
     */
    protected $provider;
    
    /**
     * @see CacheAbstract::__construct()
     * @param array|\ArrayAccess $provider
     */
    public function __construct($identifier, &$provider) 
    {
        parent::__construct($identifier);
        $this->provider = &$provider;
    }

    /**
     * @see CacheAbstract::has()
     */
    public function has($key)
    {
        return ArrUtils::has([$this->identifier, $key], $this->provider);
    }
    
    /**
     * @see CacheAbstract::get()
     */
    public function get($key, $default = null)
    {
        $value = ArrUtils::get([$this->identifier, $key], $this->provider, null);

        if (null !== $value)
        {
            return $this->getEncoder()->decode($value);
        }

        return is_callable($default) ? call_user_func($default) : $default;
    }
    
    /**
     * @see CacheAbstract::set()
     */
    public function set($key, $value, $TTL = 0)
    {
        ArrUtils::set(
            [$this->identifier, $key], 
            $this->getEncoder()->encode($value),
            $this->provider
        );
    }
    
    /**
     * @see CacheAbstract::remove()
     */
    public function remove($key)
    {
        ArrUtils::remove([$this->identifier, $key], $this->provider);
    }
    
    /**
     * @see CacheAbstract::has()
     */
    public function clear()
    {
        ArrUtils::remove($this->identifier, $this->provider);
    }
}