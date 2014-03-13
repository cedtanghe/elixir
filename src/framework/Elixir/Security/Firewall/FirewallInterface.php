<?php

namespace Elixir\Security\Firewall;

use Elixir\Dispatcher\DispatcherInterface;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

interface FirewallInterface extends DispatcherInterface
{
    /**
     * @param boolean $pWithInfos
     * @return array
     */
    public function getAccessControls($pWithInfos = false);
    
    /**
     * @param string $pResource
     * @return boolean
     */
    public function analyze($pResource);
}
