<?php

namespace Elixir\Security\Firewall\Loader;

use Elixir\Security\Firewall\Loader\Arr;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class JSON extends Arr
{
    /**
     * @see Arr::load()
     */
    public function load($pConfig)
    {
        if(is_file($pConfig))
        {
            $pConfig = file_get_contents($pConfig);
        }
        
        $pConfig = json_decode($pConfig, true);
        
        return parent::load($pConfig);
    }
}
