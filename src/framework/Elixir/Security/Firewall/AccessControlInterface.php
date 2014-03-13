<?php

namespace Elixir\Security\Firewall;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
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
