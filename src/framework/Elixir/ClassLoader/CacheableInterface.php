<?php

namespace Elixir\ClassLoader;

use Elixir\Cache\CacheInterface;
use Elixir\HTTP\Session\SessionInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
interface CacheableInterface 
{
    /**
     * @var string
     */
    const DEFAULT_CACHE_KEY = '___CACHE_LOADER___';
    
    /**
     * @param string|numeric|null $value
     */
    public function setCacheVersion($value);
    
    /**
     * @param CacheInterface|SessionInterface $cache
     * @param string $key
     */
    public function loadFromCache($cache, $key = self::DEFAULT_CACHE_KEY);
    
    /**
     * @param CacheInterface|SessionInterface $cache
     * @param string $key
     */
    public function exportToCache($cache, $key = self::DEFAULT_CACHE_KEY);
}
