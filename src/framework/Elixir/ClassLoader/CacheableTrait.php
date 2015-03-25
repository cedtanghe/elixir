<?php

namespace Elixir\ClassLoader;

use Elixir\Cache\CacheInterface;
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
     * @param string|numeric|null $value
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
     * @param CacheInterface|SessionInterface $cache
     * @param string $key
     */
    public function loadFromCache($cache, $key = '___CACHE_LOADER___')
    {
        $data = $cache->get($key, []) ?: [];
        $version = Arr::get('cache_version', $data);
        
        if(null === $this->cacheVersion || null === $version || $version === $this->cacheVersion)
        {
            if(null !== $version)
            {
                $this->_cacheVersion = $version;
            }
            
            $this->_classes = array_merge(
                Arr::get('classes', $data, []),
                $this->_classes
            );
        }
    }
    
    /**
     * @param CacheInterface|SessionInterface $cache
     * @param string $key
     */
    public function exportToCache($cache, $key = '___CACHE_LOADER___')
    {
        $cache->set(
            $key, 
            [
                'classes' => $this->_classes,
                'cache_version' => $this->cacheVersion
            ]
        );
    }
}
