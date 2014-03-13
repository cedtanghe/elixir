<?php

namespace Elixir\I18N\Loader;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
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