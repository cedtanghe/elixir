<?php

namespace Elixir\Config\Loader;

use Elixir\Config\Loader\Arr;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class INI extends Arr
{
    /**
     * @see Arr::load()
     */
    public function load($pConfig, $pRecursive = false)
    {
        if(is_file($pConfig))
        {
            $pConfig = parse_ini_file($pConfig, true);
        }
        else
        {
            $pConfig = parse_ini_string($pConfig, true);
        }
        
        return parent::load($pConfig, $pRecursive);
    }
}
