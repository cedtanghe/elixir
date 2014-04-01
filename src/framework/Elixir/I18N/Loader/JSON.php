<?php

namespace Elixir\I18N\Loader;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class JSON implements LoaderInterface
{
    /**
     * @see LoaderInterface::load()
     */
    public function load($pResource)
    {
        if(is_file($pResource))
        {
            $pResource = file_get_contents($pResource);
        }
        
        return json_decode($pResource, true);
    }
}