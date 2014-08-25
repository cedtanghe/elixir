<?php

namespace Elixir\Security\Firewall\Writer;

use Elixir\Security\Firewall\FirewallInterface;
use Elixir\Security\Firewall\Writer\WriterInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

abstract class WriterAbstract implements WriterInterface
{
    /**
     * @var FirewallInterface
     */
    protected $_firewall;
    
    /**
     * @param FirewallInterface $pFirewall
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
