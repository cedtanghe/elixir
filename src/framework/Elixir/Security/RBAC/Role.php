<?php

namespace Elixir\Security\RBAC;

use Elixir\Security\RBAC\RBACInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Role
{
    /**
     * @var string
     */
    const ALL_PERMISSIONS_GRANTED = 'all_permissions_granted';
    
    /**
     * @var string|integer
     */
    protected $_name;
    
    /**
     * @var RBACInterface
     */
    protected $_RBAC;
    
    /**
     * @var array
     */
    protected $_permissions = [];
    
    /**
     * @var array
     */
    protected $_children = [];
    
    /**
     * @param string|integer $pName
     * @param array $pPermisions
     */
    public function __construct($pName, array $pPermisions = [])
    {
        $this->_name = $pName;
        $this->setPermissions($pPermisions);
    }
    
    /**
     * @return string|integer
     */
    public function getName()
    {
        return $this->_name;
    }
    
    /**
     * @internal
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
     * @param string|integer $pName
     * @return boolean
     */
    public function hasChild($pName)
    {
        return isset($this->_children[$pName]);
    }
    
    /**
     * @internal
     * @param Role $pRole
     */
    public function addChild(self $pRole)
    {
        $this->_children[$pRole->getName()] = $pRole;
    }
    
    /**
     * @internal
     * @param string|integer $pName
     */
    public function removeChild($pName)
    {
        unset($this->_children[$pName]);
    }
    
    /**
     * @param string|integer $pName
     * @return boolean
     */
    public function hasPermission($pName)
    {
        foreach([$pName, self::ALL_PERMISSIONS_GRANTED] as $permission)
        {
            if(array_key_exists($permission, $this->_permissions))
            {
                if(true !== $this->_permissions[$permission])
                {
                    if(!$this->_permissions[$permission]($this->_RBAC))
                    {
                        continue;
                    }
                }

                return true;
            }
        }

        foreach($this->_children as $child)
        {
            if($child->hasPermission($pName))
            {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * @param string|integer $pName
     * @param callable $pAssert
     */
    public function addPermission($pName, callable $pAssert = null)
    {
        if(null !== $pAssert)
        {
            $this->_permissions[$pName] = $pAssert;
        }
        else
        {
            $this->_permissions[$pName] = true;
        }
    }
    
    /**
     * @param string|integer $pName
     */
    public function removePermission($pName)
    {
        unset($this->_permissions[$pName]);
    }
    
    /**
     * @return array
     */
    public function getPermissions()
    {
        return $this->_permissions;
    }
    
    /**
     * @param array $pData
     */
    public function setPermissions(array $pData)
    {
        $this->_permissions = [];
        
        foreach($pData as $data)
        {
            if(is_array($data))
            {
                $this->addPermission($data['permission'], isset($data['assert']) ? $data['assert'] : null);
            }
            else
            {
                $this->addPermission($data);
            }
        }
    }
}
