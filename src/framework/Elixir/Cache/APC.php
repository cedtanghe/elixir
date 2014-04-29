<?php

namespace Elixir\Cache;

use Elixir\Cache\CacheAbstract;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class APC extends CacheAbstract
{
    /**
     * @see CacheAbstract::__construct()
     * @throws \RuntimeException
     */
    public function __construct($pIdentifier) 
    {
        if(!(extension_loaded('apc') && ini_get('apc.enabled')))
        {
            throw new \RuntimeException('APC is not available.');
        }
        
        parent::__construct($pIdentifier);
    }
    
    /**
     * @see CacheInterface::has()
     */
    public function has($pKey)
    {
        return apc_exists($this->_identifier . $pKey);
    }
    
    /**
     * @see CacheInterface::get()
     */
    public function get($pKey, $pDefault = null)
    {
        $result = apc_fetch($this->_identifier . $pKey, $success);
        
        if($success)
        {
            if(null !== $this->_encoder)
            {
                $result = $this->getEncoder()->decode($result);
            }
            
            return $result;
        }
        
        return is_callable($pDefault) ? $pDefault() : $pDefault;
    }
    
    /**
     * @see CacheInterface::set()
     */
    public function set($pKey, $pValue, $pTTL = 0)
    {
        $pTTL = $this->convertTTL($pTTL);
        
        if(null !== $this->_encoder)
        {
            $pValue = $this->getEncoder()->encode($pValue);
        }
        
        apc_store($this->_identifier . $pKey, $pValue, $pTTL);
    }
    
    /**
     * @param string $pKey
     * @param integer $pStep
     * @return integer|null
     */
    public function incremente($pKey, $pStep = 1)
    {
        apc_inc($this->_identifier . $pKey, $pStep);
        return $this->get($pKey);
    }
    
    /**
     * @param string $pKey
     * @param integer $pStep
     * @return integer|null
     */
    public function decremente($pKey, $pStep = 1)
    {
        apc_dec($this->_identifier . $pKey, $pStep);
        return $this->get($pKey);
    }

    /**
     * @see CacheInterface::remove()
     */
    public function remove($pKey)
    {
        apc_delete($pKey);
    }
    
    /**
     * @see CacheInterface::has()
     */
    public function clear()
    {
        apc_clear_cache('user');
    }
}