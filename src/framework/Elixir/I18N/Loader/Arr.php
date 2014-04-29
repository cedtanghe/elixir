<?php

namespace Elixir\I18N\Loader;

use Elixir\I18N\Loader\LoaderInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Arr implements LoaderInterface
{
    /**
     * @see LoaderInterface::load()
     */
    public function load($pResource)
    {
        if(!is_array($pResource))
        {
            $pResource = include $pResource;
        }
        
        return $pResource;
    }
}