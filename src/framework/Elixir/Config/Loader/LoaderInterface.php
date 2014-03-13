<?php

namespace Elixir\Config\Loader;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

interface LoaderInterface 
{
    /**
     * @param mixed $pConfig
     * @param boolean $pRecursive
     * @return array
     */
    public function load($pConfig, $pRecursive = false);
}