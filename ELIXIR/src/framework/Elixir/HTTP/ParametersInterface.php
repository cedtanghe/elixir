<?php

namespace Elixir\HTTP;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface ParametersInterface 
{
    /**
     * @param mixed $pKey
     */
    public function has($pKey);
    
    /**
     * @param mixed $pKey
     * @param mixed $pDefault
     * @param mixed $pSanitize
     * @return mixed
     */
    public function get($pKey, $pDefault = null, $pSanitize = null);
    
    /**
     * @param mixed $pKey
     * @param mixed $pValue
     */
    public function set($pKey, $pValue);
    
    /**
     * @param mixed $pKey
     */
    public function remove($pKey);
    
    /**
     * @param mixed $pSanitize
     * @return array
     */
    public function gets($pSanitize = null);
    
    /**
     * @param array $pData
     */
    public function sets(array $pData);
    
    /**
     * @param ParametersInterface|array
     * @param boolean $pRecursive
     */
    public function merge($pData, $pRecursive = false);
}
