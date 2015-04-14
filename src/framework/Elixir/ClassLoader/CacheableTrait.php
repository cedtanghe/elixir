<?php

namespace Elixir\ClassLoader;

use Elixir\Cache\CacheInterface;
use Elixir\ClassLoader\CacheableInterface;
use Elixir\HTTP\Session\SessionInterface;
use Elixir\Util\Arr;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
trait CacheableTrait 
{
    /**
     * @var CacheInterface|SessionInterface 
     */
    protected $cache;
    
    /**
     * @var string|numeric|null
     */
    protected $cacheVersion = null;
    
    /**
     * @var string 
     */
    protected $cacheKey;
    
    /**
     * @return CacheInterface|SessionInterface 
     */
    public function getCache()
    {
        return $this->cache;
    }
    
    /**
     * @return string|numeric|null
     */
    public function getCacheVersion()
    {
        return $this->cacheVersion;
    }
    
    /**
     * @return string
     */
    public function getCacheKey()
    {
        return $this->cacheKey;
    }
    
    /**
     * @see CacheableInterface::loadFromCache()
     */
    public function loadFromCache($cache, $version = null, $key = self::DEFAULT_CACHE_KEY)
    {
        $this->cache = $cache;
        $this->cacheVersion = $version;
        $this->cacheKey = $key;
        
        $data = $this->cache->get($this->cacheKey, []) ?: [];
        $version = Arr::get('version', $data);
        
        if (null === $this->cacheVersion || null === $version || $version === $this->cacheVersion)
        {
            if (null !== $version)
            {
                $this->cacheVersion = $version;
            }
            
            $this->classes = array_merge(
                Arr::get('classes', $data, []),
                $this->classes
            );
            
            return true;
        }
        
        return false;
    }
    
    /**
     * @see CacheableInterface::loadFromCache()
     */
    public function exportToCache()
    {
        if (null !== $this->cache)
        {
            $this->cache->set(
                $this->cacheKey, 
                [
                    'classes' => $this->classes,
                    'version' => $this->cacheVersion
                ]
            );

            return true;
        }
        
        return false;
    }
    
    /**
     * @see CacheableInterface::loadFromCache()
     */
    public function invalidateCache()
    {
        if (null !== $this->cache)
        {
            $this->cache->remove($this->cacheKey);
            return true;
        }
        
        return false;
    }
}
