<?php

namespace Elixir\Security\Firewall\Behavior;

use Elixir\MVC\Exception\ForbiddenException;
use Elixir\Security\Firewall\FirewallInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class AccessForbidden
{
    /**
     * @var string 
     */
    protected $_message;

    /**
     * @param string $pMessage
     */
    public function __construct($pMessage = 'You do not have permission to access this resource.') 
    {
        $this->_message = $pMessage;
    }
    
    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }
    
    /**
     * @param FirewallInterface $pFirewall
     * @throws ForbiddenException
     */
    public function __invoke(FirewallInterface $pFirewall)
    {
        throw new ForbiddenException($this->_message);
    }
}
