<?php

namespace Elixir\Config\Loader;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
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
