<?php

namespace Elixir\Security\RBAC;

use Elixir\Security\RBAC\RBACInterface;
use Elixir\Security\RBAC\Role;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class RBAC implements RBACInterface
{
    /**
     * @var array
     */
    protected $_roles = [];
    
    /**
     * @param array $pRoles
     */
    public function __construct(array $pRoles = []) 
    {
        $this->setRoles($pRoles);
    }

    /**
     * @see RBACInterface::hasRole()
     */
    public function hasRole($pRole)
    {
        return isset($this->_roles[(string)$pRole]);
    }
    
    /**
     * @param mixed $pRole
     * @param string|array $pParents
     */
    public function addRole($pRole, $pParents = [])
    {
        if(!$pRole instanceof Role)
        {
            $pRole = new Role($pRole);
        }
        
        $pRole->setRBAC($this);
        $this->_roles[$pRole->getName()] = $pRole;
        
        foreach((array)$pParents as $parent)
        {
            if(!$parent instanceof Role)
            {
                $parent = new Role($parent);
            }
            
            $this->addRole($parent);
            $this->getRole($parent->getName())->addChild($pRole);
        }
    }
    
    /**
     * @param string|integer $pRole
     * @return boolean
     */
    public function removeRole($pRole)
    {
        unset($this->_roles[$pRole]);
        
        foreach($this->_roles as $role)
        {
            $role->removeChild($pRole);
        }
    }
    
    /**
     * @param string|integer $pRole
     * @param mixed $pDefault
     * @return Role
     */
    public function getRole($pRole, $pDefault = null)
    {
        if($this->hasRole($pRole))
        {
            return $this->_roles[$pRole];
        }
        
        return is_callable($pDefault) ? $pDefault() : $pDefault;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->_roles;
    }
    
    /**
     * @param array $pData
     */
    public function setRoles(array $pData)
    {
        $this->_roles = [];
        
        foreach($pData as $data)
        {
            $role = $data;
            $permissions = [];
            $parents = [];
            
            if(is_array($role))
            {
                $role = $role['role'];
                
                if(isset($role['permissions']))
                {
                    $permissions = $role['permissions'];
                }
                
                if(isset($role['parents']))
                {
                    $parents = $role['parents'];
                }
            }
            
            $r = new Role($role);
            
            if(count($permissions) > 0)
            {
                $r->setPermissions($permissions);
            }
            
            $this->addRole($r, $parents);
        }
    }
    
    /**
     * @see RBACInterface::isGranted()
     * @throws \InvalidArgumentException
     */
    public function isGranted($pRole, $pPermission = null, $pAssert = null)
    {
        if($this->hasRole($pRole))
        {
            $hasRole = true;
            
            if(null !== $pPermission)
            {
                $hasPermission = $this->getRole($pRole)->hasPermission($pPermission);
            }
            else 
            {
                $hasPermission = true;
            }
        }
        else 
        {
            $hasRole = false;
            $hasPermission = false;
        }
        
        if(null !== $pAssert)
        {
            if(!is_callable($pAssert))
            {
                throw new \InvalidArgumentException('Assert method must be a callable.');
            }

            if(true === call_user_func_array($pAssert, [$hasRole, $hasPermission, $this]))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        
        return $hasRole && $hasPermission;
    }
}
