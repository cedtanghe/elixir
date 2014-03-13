<?php

namespace Elixir\Cache;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

interface CacheInterface
{
    /**
     * @param string $pKey 
     * @return boolean
     */
    public function has($pKey);
    
    /**
     * @param string $pKey
     * @param mixed $pDefault 
     * @return mixed
     */
    public function get($pKey, $pDefault = null);
    
    /**
     * @param string $pKey
     * @param mixed $pValue
     * @param integer|string|\DateTime $pTTL 
     */
    public function set($pKey, $pValue, $pTTL = 0);

    /**
     * @param string $pKey 
     */
    public function remove($pKey);
    
    public function clear();
}