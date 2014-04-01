<?php

namespace Elixir\Security\Firewall;

use Elixir\Dispatcher\Event;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class FirewallEvent extends Event
{
    /**
     * @var string
     */
    const ANALYSE = 'pre_analyse';
    
    /**
     * @var string
     */
    const RESOURCE_MATCHED = 'resource_matched';
    
    /**
     * @var string
     */
    const ACCESS_GRANTED = 'access_granted';
    
    /**
     * @var string
     */
    const ACCESS_FORBIDDEN = 'access_forbidden';
    
    /**
     * @var string
     */
    const IDENTITY_NOT_FOUND = 'identity_not_found';
    
    /**
     * @var string
     */
    const NO_ACCESS_CONTROLS_FOUND = 'no_access_controls_found';
    
    /**
     * @var string
     */
    protected $_resource;

    /**
     * @var AccessControlInterface
     */
    protected $_accessControl;

    /**
     * @see Event::__contruct()
     * @param string $pResource
     * @param AccessControlInterface $pAccessControl
     */
    public function __construct($pType, $pResource = null, AccessControlInterface $pAccessControl = null)
    {
        parent::__construct($pType);
        
        $this->_resource = $pResource;
        $this->_accessControl = $pAccessControl;
    }
    
    /**
     * @return string
     */
    public function getResource()
    {
        return $this->_resource;
    }
    
    /**
     * @return  AccessControlInterface
     */
    public function getAccessControl()
    {
        return $this->_accessControl;
    }
}
