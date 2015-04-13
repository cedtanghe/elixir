<?php

namespace Elixir\Config\Cache;

use Elixir\Config\ConfigInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
interface CacheableInterface 
{
    /**
     * @param ConfigInterface $value
     */
    public function setConfig(ConfigInterface $value);
    
    /**
     * @return boolean
     */
    public function loadCache();
    
    /**
     * @return boolean
     */
    public function cacheLoaded();
    
    /**
     * @param mixed $file
     * @return array
     */
    public function loadFromCache($file, array $options = []);
    
    /**
     * @return boolean
     */
    public function exportToCache();
}
