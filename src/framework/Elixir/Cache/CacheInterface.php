<?php

namespace Elixir\Cache;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
interface CacheInterface
{
    /**
     * @var integer
     */
    const DEFAULT_TTL = 31556926;
    
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
     * @param integer|string|\DateTime $ttl
     * @return boolean
     */
    public function set($key, $value, $ttl = self::DEFAULT_TTL);
    
    /**
     * @param string $key
     * @param mixed $value
     * @param integer|string|\DateTime $ttl 
     * @return mixed
     */
    public function remember($key, $value, $ttl = self::DEFAULT_TTL);

    /**
     * @param string $key 
     * @return boolean
     */
    public function remove($key);
    
    /**
     * @param string $key
     * @param integer $step
     * @return integer
     */
    public function incremente($key, $step = 1);
    
    /**
     * @param string $key
     * @param integer $step
     * @return integer
     */
    public function decremente($key, $step = 1);
    
    /**
     * @return boolean
     */
    public function flush();
}
