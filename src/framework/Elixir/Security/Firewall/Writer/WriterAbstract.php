<?php

namespace Elixir\Security\Firewall\Writer;

use Elixir\Security\Firewall\FirewallInterface;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

abstract class WriterAbstract implements WriterInterface
{
    /**
     * @var FirewallInterface
     */
    protected $_firewall;
    
    /**
     * @param FirewallInterface $pRouter
     */
    public function __construct(FirewallInterface $pFirewall = null)
    {
        $this->_firewall = $pFirewall;
    }

    /**
     * @param FirewallInterface $pValue
     */
    public function setFirewall(FirewallInterface $pValue)
    {
        $this->_firewall = $pValue;
    }
    
    /**
     * @return FirewallInterface
     */
    public function getFirewall()
    {
        return $this->_firewall;
    }
}