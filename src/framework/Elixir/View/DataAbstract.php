<?php

namespace Elixir\View;

use Elixir\View\GlobalInterface;
use Elixir\View\ViewInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

abstract class DataAbstract implements ViewInterface, GlobalInterface
{
    /**
     * @var array 
     */
    protected $_vars = [];
    
    /**
     * @var array 
     */
    protected $_global = [];
    
    /**
     * @see ViewInterface::has()
     */
    public function has($pKey)
    {
        return isset($this->_vars[$pKey]);
    }
    
    /**
     * @see ViewInterface::get()
     */
    public function get($pKey, $pDefault = null)
    {
        if($this->has($pKey))
        {
            return $this->_vars[$pKey];
        }
        
        return is_callable($pDefault) ? $pDefault() : $pDefault;
    }
    
    /**
     * @see ViewInterface::set()
     */
    public function set($pKey, $pValue)
    {
        $this->_vars[$pKey] = $pValue;
    }
    
    /**
     * @see GlobalInterface::setGlobal()
     */
    public function setGlobal($pKey, $pValue)
    {
        $this->set($pKey, $pValue);
        $this->globalize($pKey);
    }
    
    /**
     * @see ViewInterface::remove()
     */
    public function remove($pKey)
    {
        unset($this->_vars[$pKey]);
        unset($this->_global[$pKey]);
    }
    
    /**
     * @see ViewInterface::gets()
     */
    public function gets()
    {
        $values = $this->_vars;
        
        if($this->_autoEscape)
        {
            $values = $this->escape($values);
        }
        
        return $values;
    }
    
    /**
     * @see ViewInterface::sets()
     */
    public function sets(array $pData)
    {
        $this->_vars = [];
        
        foreach($pData as $key => $value)
        {
            $this->set($key, $value);
        }
        
        foreach($this->_global as $key => $value)
        {
            if(!$this->has($key))
            {
                unset($this->_global[$key]);
            }
        }
    }
    
    /**
     * @see GlobalInterface::globalize()
     */
    public function globalize($pKey)
    {
        if($this->has($pKey))
        {
            $this->_global[$pKey] = true;
        }
    }
    
    /**
     * @see GlobalInterface::isGlobal()
     */
    public function isGlobal($pKey)
    {
        return array_key_exists($pKey, $this->_global);
    }

    /**
     * @see DataAbstract::has()
     */
    public function __isset($pKey) 
    {
        return $this->has($pKey);
    }
    
    /**
     * @see DataAbstract::get()
     */
    public function __get($pKey)
    {
        return $this->get($pKey);
    }

    /**
     * @see DataAbstract::set()
     */
    public function __set($pKey, $pValue)
    {
        $this->set($pKey, $pValue);
    }
    
    /**
     * @see DataAbstract::remove()
     */
    public function __unset($pKey) 
    {
        $this->remove($pKey);
    }
    
    public function __clone()
    {
        $this->_vars = array_intersect_key($this->_vars, $this->_global);
    }
}