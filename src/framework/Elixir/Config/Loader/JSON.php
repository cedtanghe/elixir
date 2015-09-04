<?php

namespace Elixir\Config\Loader;

use Elixir\Config\Loader\Arr;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class JSON extends Arr 
{
    /**
     * @see Arr::load()
     */
    public function load($config, $recursive = false)
    {
        if (is_file($config)) 
        {
            $config = file_get_contents($config);
        }
        
        return parent::load(json_decode($config, true), $recursive);
    }
}
