<?php

namespace Elixir\Security\Firewall;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface AccessControlInterface
{
    /**
     * @return string
     */
    public function getPattern();
    
    /**
     * @return array
     */
    public function getOptions();
}
