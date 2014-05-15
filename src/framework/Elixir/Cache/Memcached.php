<?php

namespace Elixir\Cache;

use Elixir\Cache\CacheAbstract;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Memcached extends CacheAbstract
{
    /**
     * @var \Memcached
     */
    protected $_memcached;

    /**
     * @see CacheAbstract::__construct()
     * @throws \RuntimeException
     */
    public function __construct($pIdentifier) 
    {
        if(!class_exists('\Memcached'))
        {
            throw new \RuntimeException('Memcached is not available.');
        }
        
        parent::__construct($pIdentifier);
        $this->_memcached = new \Memcached($this->_identifier);
    }
    
    public function __destruct() 
    {
        $this->_memcached = null;
    }

    /**
     * @see CacheInterface::has()
     */
    public function has($pKey)
    {
        if(!$this->_memcached->get($pKey))
        {
            return $this->_memcached->getResultCode() == \Memcached::RES_NOTFOUND;
        }
        
        return true;
    }
    
    /**
     * @see CacheInterface::get()
     */
    public function get($pKey, $pDefault = null)
    {
        return $this->_memcached->get($pKey, $pDefault);
    }
    
    /**
     * @see CacheInterface::set()
     */
    public function set($pKey, $pValue, $pTTL = 0)
    {
        if($pTTL != 0)
        {
            $pTTL = time() + $this->convertTTL($pTTL);
        }
        
        if(null !== $this->_encoder)
        {
            $pValue = $this->getEncoder()->encode($pValue);
        }
        
        $this->_memcached->set($pKey, $pValue, $pTTL);
    }
    
    /**
     * @param string $pKey
     * @param integer $pStep
     * @return integer|null
     */
    public function incremente($pKey, $pStep = 1)
    {
        $this->_memcached->increment($pKey, $pStep);
        return $this->get($pKey);
    }
    
    /**
     * @param string $pKey
     * @param integer $pStep
     * @return integer|null
     */
    public function decremente($pKey, $pStep = 1)
    {
        $this->_memcached->decrement($pKey, $pStep);
        return $this->get($pKey);
    }

    /**
     * @see CacheInterface::remove()
     */
    public function remove($pKey)
    {
        $this->_memcached->delete($key);
    }
    
    /**
     * @see CacheInterface::has()
     */
    public function clear()
    {
        $this->_memcached->flush();
    }
    
    /**
     * @param string $pMethod
     * @param array $pArguments
     */
    public function __call($pMethod, $pArguments) 
    {
        return call_user_func_array([$this->_memcached, $pMethod], $pArguments);
    }
}