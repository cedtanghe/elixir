<?php

namespace Elixir\DB\ORM;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

interface EntityInterface
{
    /**
     * @param array $pData
     * @param boolean $pRaw
     */
    public function hydrate(array $pData, $pRaw = true);
    
    /**
     * @param array $pMembers
     * @param array $pOmitMembers
     * @param boolean $pRaw
     * @return array
     */
    public function export(array $pMembers = array(), array $pOmitMembers = array(), $pRaw = true);
    
    /**
     * @param string $pKey
     * @return boolean
     */
    public function has($pKey);
    
    /**
     * @param string $pKey
     * @param mixed $pValue
     * @param boolean $pFilled
     */
    public function set($pKey, $pValue, $pFilled = true);
    
    /**
     * @param string $pKey
     * @return mixed
     */
    public function get($pKey);
}