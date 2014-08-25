<?php

namespace Elixir\Security\Firewall\RBAC;

use Elixir\Security\Firewall\AccessControlAbstract;
use Elixir\Security\Firewall\AccessControlInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class AccessControl extends AccessControlAbstract
{
    /**
     * @var string
     */
    protected $_pattern;
    
    /**
     * @var array
     */
    protected $_options = [
        'roles' => [],
        'permissions' => [],
        'assert' => null,
        'domains' => []
    ];
    
    /**
     * @param string $pPattern
     * @param array $pOptions
     */
    public function __construct($pPattern, array $pOptions = [])
    {
        $this->_pattern = $pPattern;
        
        foreach($pOptions as $key => $value)
        {
            $this->setOption($key, $value);
        }
    }
    
    /**
     * @see AccessControlInterface::getPattern()
     */
    public function getPattern()
    {
        return $this->_pattern;
    }
    
    /**
     * @see AccessControlAbstract::setOption()
     */
    public function setOption($pKey, $pValue)
    {
        if($pKey === 'roles')
        {
            if(null === $pValue)
            {
                $pValue = [];
            }
            
            $this->setRoles((array)$pValue);
        }
        else if($pKey === 'permissions')
        {
            if(null === $pValue)
            {
                $pValue = [];
            }
            
            $this->setPermissions((array)$pValue);
        }
        else if($pKey === 'assert')
        {
            $this->setAssert($pValue);
        }
        else if($pKey === 'domains')
        {
            if(null === $pValue)
            {
                $pValue = [];
            }
            
            $this->setDomains((array)$pValue);
        }
        else
        {
            parent::setOption($pKey, $pValue);
        }
    }
    
    /**
     * @param string|integer $pRole
     */
    public function addRole($pRole)
    {
        if(!in_array($pRole, $this->_options['roles']))
        {
            $this->_options['roles'][] = $pRole;
        }
    }
    
    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->_options['roles'];
    }
    
    /**
     * @param array $pData
     */
    public function setRoles(array $pData)
    {
        $this->_options['roles'] = [];
        
        foreach($pData as $role)
        {
            $this->addRole($role);
        }
    }
    
    /**
     * @param string|integer $pPermission
     */
    public function addPermission($pPermission)
    {
        if(!in_array($pPermission, $this->_options['permissions']))
        {
            $this->_options['permissions'][] = $pPermission;
        }
    }
    
    /**
     * @return array
     */
    public function getPermissions()
    {
        return $this->_options['permissions'];
    }
    
    /**
     * @param array $pData
     */
    public function setPermissions(array $pData)
    {
        $this->_options['permissions'] = [];
        
        foreach($pData as $permission)
        {
            $this->addPermission($permission);
        }
    }
    
    /**
     * @param callable $pValue
     * @throws \InvalidArgumentException
     */
    public function setAssert($pValue)
    {
        if(!is_callable($pValue))
        {
            throw new \InvalidArgumentException(sprintf('Assert method for "%s" must be a callable.', $this->_pattern));
        }
            
        $this->_options['assert'] = $pValue;
    }
    
    /**
     * @return callable
     */
    public function getAssert()
    {
        return $this->_options['assert'];
    }
    
    /**
     * @param string $pDomain
     */
    public function addDomain($pDomain)
    {
        if(!in_array($pDomain, $this->_options['domains']))
        {
            $this->_options['domains'][] = $pDomain;
        }
    }
    
    /**
     * @return array
     */
    public function getDomains()
    {
        return $this->_options['domains'];
    }
    
    /**
     * @param array $pData
     */
    public function setDomains(array $pData )
    {
        $this->_options['domains'] = [];
        
        foreach($pData as $domain)
        {
            $this->addDomain($domain);
        }
    }
}
