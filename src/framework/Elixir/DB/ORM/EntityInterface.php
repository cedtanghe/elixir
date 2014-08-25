<?php

namespace Elixir\DB\ORM;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface EntityInterface
{
    /**
     * @param array $pData
     * @param array $pOptions
     */
    public function hydrate(array $pData, array $pOptions = ['raw' => true]);
    
    /**
     * @param array $pMembers
     * @param array $pOmitMembers
     * @param boolean $pRaw
     * @return array
     */
    public function export(array $pMembers = [], array $pOmitMembers = [], $pRaw = false);
    
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
