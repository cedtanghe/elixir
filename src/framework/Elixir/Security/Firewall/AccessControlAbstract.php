<?php

namespace Elixir\Security\Firewall;

use Elixir\Security\Firewall\AccessControlInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

abstract class AccessControlAbstract implements AccessControlInterface
{
    /**
     * @var array
     */
    protected $_options = [];
    
    /**
     * @param string $pKey
     * @return boolean
     */
    public function hasOption($pKey)
    {
        return array_key_exists($pKey, $this->_options);
    }
    
    /**
     * @param string $pKey
     * @param mixed $pValue
     */
    public function setOption($pKey, $pValue)
    {
        $this->_options[$pKey] = $pValue;
    }
    
    /**
     * @param string $pKey
     * @param mixed $pDefault
     * @return mixed
     */
    public function getOption($pKey, $pDefault = null)
    {
        if($this->hasOption($pKey))
        {
            return $this->_options[$pKey];
        }
        
        return is_callable($pDefault) ? $pDefault() : $pDefault;
    }
    
    /**
     * @param string $pKey
     */
    public function removeOption($pKey)
    {
        unset($this->_options[$pKey]);
    }
    
    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }
    
    /**
     * @param array $pData
     */
    public function setOptions(array $pData)
    {
        $this->_options = [];
        
        foreach($pData as $key => $value)
        {
            $this->setOption($key, $value);
        }
    }
}
