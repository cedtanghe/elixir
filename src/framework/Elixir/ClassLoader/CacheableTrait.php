<?php

namespace Elixir\ClassLoader;

use Elixir\ClassLoader\CacheableInterface;
use Elixir\Routing\Loader\Arr;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
trait CacheableTrait 
{
    /**
     * @var string|numeric|null
     */
    protected $cacheVersion = null;
    
    /**
     * @see CacheableInterface::setCacheVersion()
     */
    public function setCacheVersion($value)
    {
        $this->cacheVersion = $value;
    }
    
    /**
     * @return string|numeric|null
     */
    public function getCacheVersion()
    {
        return $this->cacheVersion;
    }
    
    /**
     * @see CacheableInterface::loadFromCache()
     */
    public function loadFromCache($cache, $key = self::DEFAULT_CACHE_KEY)
    {
        $data = $cache->get($key, []) ?: [];
        $version = Arr::get('cache_version', $data);
        
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
        }
    }
    
    /**
     * @see CacheableInterface::loadFromCache()
     */
    public function exportToCache($cache, $key = self::DEFAULT_CACHE_KEY)
    {
        $cache->set(
            $key, 
            [
                'classes' => $this->classes,
                'cache_version' => $this->cacheVersion
            ]
        );
    }
}
