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
     * @param mixed $file
     * @return array
     */
    public function loadFromCache($file);
    
    /**
     * @return boolean
     */
    public function exportToCache();
}
