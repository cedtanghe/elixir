<?php

namespace Elixir\Security\RBAC;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

interface RBACInterface
{
    /**
     * @param string|integer $pRole
     * @return boolean
     */
    public function hasRole($pRole);
    
    /**
     * @return array
     */
    public function getRoles();
    
    /**
     * @param string|integer $pRole
     * @param string|integer $pPermission
     * @param callable $pAssert
     * @return boolean
     */
    public function isGranted($pRole, $pPermission = null, $pAssert = null);
}
