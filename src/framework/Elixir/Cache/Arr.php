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
     * @param array|\ArrayAccess $provider
     */
    public function __construct(&$provider) 
    {
        $this->provider = &$provider;
    }

    /**
     * @see CacheAbstract::exists()
     */
    public function exists($key)
    {
        return ArrUtils::has($key, $this->provider);
    }
    
    /**
     * @see CacheAbstract::get()
     */
    public function get($key, $default = null)
    {
        $value = ArrUtils::get($key, $this->provider, null);

        if (null !== $value)
        {
            return $this->encoder->decode($value);
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
            $value = $this->encoder->encode($value);
        }
        
        ArrUtils::set($key, $value, $this->provider);
        return true;
    }
    
    /**
     * @see CacheAbstract::delete()
     */
    public function delete($key)
    {
        ArrUtils::remove($key, $this->provider);
        return true;
    }
    
    /**
     * @see CacheAbstract::incremente()
     */
    public function incremente($key, $step = 1)
    {
        $value = $this->get($key, null);
        
        if (null === $value)
        {
            return 0;
        }
        
        $value += $step;
        $this->store($key, $value);
        
        return $value;
    }
    
    /**
     * @see CacheAbstract::decremente()
     */
    public function decremente($key, $step = 1)
    {
        $value = $this->get($key, 0) - $step;
        
        if (null === $value)
        {
            return 0;
        }
        
        $value -= $step;
        $this->store($key, $value);
        
        return $value;
    }
    
    /**
     * @see CacheAbstract::flush()
     */
    public function flush()
    {
        $this->provider = [];
        return true;
    }
}
