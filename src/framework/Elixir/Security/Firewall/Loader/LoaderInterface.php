<?php

namespace Elixir\Security\Firewall\Loader;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
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
