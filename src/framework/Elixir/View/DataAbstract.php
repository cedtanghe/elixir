<?php

namespace Elixir\View;

use Elixir\Filter\FilterInterface;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

abstract class DataAbstract implements ViewInterface, GlobalInterface
{
    /**
     * @var array 
     */
    protected $_vars = array();
    
    /**
     * @var array 
     */
    protected $_global = array();
    
    /**
     * @var FilterInterface 
     */
    protected $_escaper;
    
    /**
     * @var boolean 
     */
    protected $_autoEscape = false;

    /**
     * @param FilterInterface $pValue
     */
    public function setEscaper(FilterInterface $pValue)
    {
        $this->_escaper = $pValue;
    }
    
    /**
     * @return FilterInterface
     */
    public function getEscaper()
    {
        return $this->_escaper;
    }
    
    /**
     * @see ViewInterface::setAutoEscape()
     */
    public function setAutoEscape($pValue)
    {
        $this->_autoEscape = $pValue;
    }
    
    /**
     * @see ViewInterface::isAutoEscape()
     */
    public function isAutoEscape()
    {
        return $this->_autoEscape;
    }

    /**
     * @see ViewInterface::escape()
     */
    public function escape($pData, $pStrategy = 'html')
    {
        if(null !== $this->_escaper)
        {
            if(is_array($pData) || is_object($pData) || $pData instanceof \Traversable)
            {
                foreach($pData as &$value)
                {
                    $value = $this->escape($value, $pStrategy);
                }
            }
            else
            {
                $pData = $this->_escaper->filter($pData, array('strategy' => $pStrategy));
            }
        }
        
        return $pData;
    }
    
    /**
     * @see ViewInterface::raw()
     */
    public function raw($pKey, $pDefault = null)
    {
        if($this->has($pKey))
        {
            return $this->_vars[$pKey];
        }
        
        if(is_callable($pDefault))
        {
            return call_user_func($pDefault);
        }
        
        return $pDefault;
    }
    
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
        $value = $this->raw($pKey, $pDefault);
        
        if($this->_autoEscape)
        {
            $value = $this->escape($value);
        }
        
        return $value;
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
        $this->_vars = array();
        
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