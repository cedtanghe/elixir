<?php

namespace Elixir\Security\Firewall\Loader;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface LoaderInterface 
{
    /**
     * @var string
     */
    const GLOBALS = '_globals';
    
    /**
     * @param mixed $pConfig
     * @return array
     */
    public function load($pConfig);
}
