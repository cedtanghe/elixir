<?php

namespace Elixir\Config;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface ConfigInterface 
{
    /**
     * @param mixed $pKey
     * @return boolean
     */
    public function has($pKey);
    
    /**
     * @param mixed $pKey
     * @param mixed $pDefault
     * @return mixed
     */
    public function get($pKey, $pDefault = null);
    
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
     * @return array
     */
    public function gets();
    
    /**
     * @param array $pData
     */
    public function sets(array $pData);
    
    /**
     * @param ConfigInterface|array
     * @param boolean $pRecursive
     */
    public function merge($pData, $pRecursive = false);
}
