<?php

namespace Elixir\Helper;

use Elixir\Helper\HelperInterface;
use Elixir\HTTP\Request;
use Elixir\Security\Authentification\Manager;
use Elixir\Security\Firewall\FirewallInterface;
use Elixir\Security\RBAC\RBACInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Security implements HelperInterface
{
    /**
     * @var string
     */
    const TEMPORARY_PARAMETERS = '_security';
    
    /**
     * @var Manager 
     */
    protected $_manager;
    
    /**
     * @var RBACInterface 
     */
    protected $_RBAC;
    
    /**
     * @var FirewallInterface 
     */
    protected $_firewall;
    
    /**
     * @var Request 
     */
    protected $_request;
    
    /**
     * @param Manager $pValue
     */
    public function setManager(Manager $pValue)
    {
        $this->_manager = $pValue;
    }
    
    /**
     * @return Manager
     */
    public function getManager()
    {
        return $this->_manager;
    }
    
    /**
     * @param Request $pValue
     */
    public function setRequest(Request $pValue)
    {
        $this->_request = $pValue;
    }
    
    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->_request;
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
     * @param FirewallInterface $pValue
     */
    public function setFirewall(FirewallInterface $pValue)
    {
        $this->_firewall = clone $pValue;
        $this->_firewall->removeListeners();
    }
    
    /**
     * @return FirewallInterface
     */
    public function getFirewall()
    {
        return $this->_firewall;
    }
    
    /**
     * @param string $pIdentity
     * @return boolean
     */
    public function hasIdentity($pIdentity)
    {
        if(null !== $this->_manager)
        {
            return $this->_manager->has($pIdentity);
        }
        
        return false;
    }
    
    /**
     * @param string $pRole
     * @param string $pIdentity
     * @return boolean
     */
    public function hasRole($pRole, $pIdentity = null)
    {
        if(null !== $pIdentity && null !== $this->_manager)
        {
            $identity = $this->_manager->get($pIdentity);

            if(null !== $identity && $identity->getSecurityContext() instanceof RBACInterface)
            {
                return $identity->getSecurityContext()->hasRole($pRole);
            }
        }
        else if(null !== $this->_RBAC)
        {
            return $this->_RBAC->hasRole($pRole);
        }
        
        return false;
    }

    /**
     * @param string $pRole
     * @param string $pPermission
     * @param callable $pAssert
     * @param string $pIdentity
     * @return boolean
     */
    public function isGrantedRole($pRole, $pPermission = null, $pAssert = null, $pIdentity = null)
    {
        if(null !== $pIdentity && null !== $this->_manager)
        {
            $identity = $this->_manager->get($pIdentity);

            if(null !== $identity && $identity->getSecurityContext() instanceof RBACInterface)
            {
                return $identity->getSecurityContext()->isGranted($pRole, $pPermission, $pAssert);
            }
        }
        else if(null !== $this->_RBAC)
        {
            return $this->_RBAC->isGranted($pRole, $pPermission, $pAssert);
        }
        
        return false;
    }
    
    /**
     * @param string $pResource
     * @param array $pTempParameters
     * @return boolean
     * @throws \RuntimeException
     */
    public function isGrantedResource($pResource, array $pTempParameters = [])
    {
        if(null !== $this->_firewall)
        {
            if(count($pTempParameters) > 0)
            {
                if(null === $this->_request)
                {
                    throw new \RuntimeException('Request component is not available.');
                }

                $this->_request->getAttributes()->set(self::TEMPORARY_PARAMETERS, $pTempParameters);
            }

            $result = $this->_firewall->analyze($pResource);

            if(null !== $this->_request)
            {
                $this->_request->getAttributes()->remove(self::TEMPORARY_PARAMETERS);
            }

            return $result;
        }
        
        return false;
    }

    /**
     * @see Security::isGrantedResource()
     */
    public function direct()
    {
        $args = func_get_args();
        return call_user_func_array([$this, 'isGrantedResource'], $args);
    }
}
