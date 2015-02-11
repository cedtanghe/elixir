<?php

namespace Elixir\Cache;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface CacheInterface
{
    /**
     * @param string $key 
     * @return boolean
     */
    public function has($key);
    
    /**
     * @param string $key
     * @param mixed $default 
     * @return mixed
     */
    public function get($key, $default = null);
    
    /**
     * @param string $key
     * @param mixed $value
     * @param integer|string|\DateTime $TTL 
     */
    public function set($key, $value, $TTL = 0);

    /**
     * @param string $key 
     */
    public function remove($key);
    
    public function clear();
}
