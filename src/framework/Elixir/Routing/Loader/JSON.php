<?php

namespace Elixir\Routing\Loader;

use Elixir\Routing\Collection;
use Elixir\Routing\Loader\Arr;
use Elixir\Routing\Loader\LoaderInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class JSON extends Arr
{
    /**
     * @see LoaderInterface::load()
     */
    public function load($pConfig, Collection $pCollection = null)
    {
        if(is_file($pConfig))
        {
            $pConfig = file_get_contents($pConfig);
        }
        
        $pConfig = json_decode($pConfig, true);
        
        return parent::load($pConfig, $pCollection);
    }
}
