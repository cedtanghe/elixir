<?php

namespace Elixir\Helper;

use Elixir\Helper\HelperInterface;
use Elixir\Security\Authentification\Manager;
use Elixir\Security\RBAC\RBACInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Security implements HelperInterface
{
    /**
     * @var Manager 
     */
    protected $_manager;
    
    /**
     * @var RBACInterface 
     */
    protected $_RBAC;
    
    /**
     * @param Manager $pManager
     */
    public function __construct(Manager $pManager)
    {
        $this->_manager = $pManager;
    }
    
    /**
     * @param string $pName
     */
    public function configureByIdentity($pName)
    {
        if($this->hasIdentity($pName))
        {
            $identity = $this->_manager->get($pName);
            
            if($identity->getSecurityContext() instanceof RBACInterface)
            {
                $this->setRBAC($identity->getSecurityContext());
            }
        }
    }

    /**
     * @param RBACInterface $pValue
     */
    public function setRBAC(RBACInterface $pValue)
    {
        $this->_RBAC = $pValue;
    }
    
    /**
     * @return RBACInterface
     */
    public function getRBAC()
    {
        return $this->_RBAC;
    }
    
    /**
     * @param string $pIdentity
     * @return boolean
     */
    public function hasIdentity($pIdentity)
    {
        return $this->_manager->has($pIdentity);
    }
    
    /**
     * @param string $pRole
     * @return boolean
     * @throws \RuntimeException
     */
    public function hasRole($pRole)
    {
        if(null === $this->_RBAC)
        {
            throw new \RuntimeException('RBAC component is not available.');
        }
        
        return $this->_RBAC->hasRole($pRole);
    }

    /**
     * @param string|integer $pRole
     * @param string|integer $pPermission
     * @param callable $pAssert
     * @return boolean
     * @throws \RuntimeException
     */
    public function isGranted($pRole, $pPermission = null, $pAssert = null)
    {
        if(null === $this->_RBAC)
        {
            throw new \RuntimeException('RBAC component is not available.');
        }
        
        return $this->_RBAC->isGranted($pRole, $pPermission, $pAssert);
    }

    /**
     * @see Security::hasIdentity()
     */
    public function direct()
    {
        $args = func_get_args();
        return call_user_func_array(array($this, 'hasIdentity'), $args);
    }
}