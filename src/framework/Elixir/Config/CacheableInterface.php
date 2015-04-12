<?php

namespace Elixir\Config;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
interface CacheableInterface 
{
    public function loadFromCache($file, array $options = []);
    public function exportCache();
}
