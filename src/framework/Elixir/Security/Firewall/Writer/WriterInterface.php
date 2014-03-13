<?php

namespace Elixir\Security\Firewall\Writer;

use Elixir\Security\Firewall\FirewallInterface;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

interface WriterInterface 
{
    /**
     * @param FirewallInterface $pValue
     */
    public function setFirewall(FirewallInterface $pValue);

    /**
     * @return mixed
     */
    public function write();
    
    /**
     * @param string $pFile
     * @return boolean
     */
    public function export($pFile);  
}