<?php

namespace Elixir\HTTP;

use Elixir\HTTP\ParametersInterface;
use Elixir\HTTP\Sanitizer;
use Elixir\Util\Arr;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Parameters implements ParametersInterface, \ArrayAccess, \Iterator, \Countable
{
    /**
     * @var array|\ArrayAccess 
     */
    protected $_data;
    
    /**
     * @var Sanitizer 
     */
    protected $_sanitizer;
    
    /**
     * @var boolean
     */
    protected $_autoSanitization = false;
    
    /**
     * @param array|\ArrayAccess $pData
     */
    public function __construct(&$pData)
    {
        $this->_data = &$pData;
    }
    
    /**
     * @param Sanitizer $pValue
     */
    public function setSanitizer(Sanitizer $pValue)
    {
        $this->_sanitizer = $pValue;
    }
    
    /**
     * @return Sanitizer
     */
    public function getSanitizer()
    {
        return $this->_sanitizer;
    }
    
    /**
     * @param boolean $pValue
     */
    public function setAutoSanitization($pValue)
    {
        $this->_autoSanitization = $pValue;
    }
    
    /**
     * @return boolean
     */
    public function isAutoSanitization()
    {
        return $this->_autoSanitization;
    }
    
    /**
     * @see ParametersInterface::has()
     */
    public function has($pKey)
    {
        return Arr::has($pKey, $this->_data);
    }
    
    /**
     * @see ParametersInterface::get()
     */
    public function get($pKey, $pDefault = null, $pSanitize = null)
    {
        if($this->has($pKey))
        {
            $v = Arr::get($pKey, $this->_data);
            
            if(null !== $pSanitize)
            {
                if(false !== $pSanitize)
                {
                    if(is_array($pSanitize))
                    {
                        $filter = $pSanitize['filter'];
                        $options = $pSanitize['options'];
                    }
                    else if(is_callable($pSanitize))
                    {
                        $filter = FILTER_CALLBACK;
                        $options = ['options' => $pSanitize];
                    }
                    else
                    {
                        $filter = $pSanitize;
                        $options = [];
                    }
                    
                    $v = $this->filter($v, $filter, $options);
                }
            }
            else if($this->_autoSanitization)
            {
                $v = $this->filter($v);
            }
            
            return $v;
        }
        
        return is_callable($pDefault) ? $pDefault() : $pDefault;
    }
    
    /**
     * @see ParametersInterface::set()
     */
    public function set($pKey, $pValue)
    {
        Arr::set($pKey, $pValue, $this->_data);
    }
    
    /**
     * @see ParametersInterface::remove()
     */
    public function remove($pKey)
    {
        Arr::remove($pKey, $this->_data);
    }
    
    /**
     * @see ParametersInterface::gets()
     */
    public function gets($pSanitize = null) 
    {
        $data = $this->_data;
            
        if(null !== $pSanitize)
        {
            if(false !== $pSanitize)
            {
                if(is_array($pSanitize))
                {
                    $filter = $pSanitize['filter'];
                    $options = $pSanitize['options'];
                }
                else if(is_callable($pSanitize))
                {
                    $filter = FILTER_CALLBACK;
                    $options = ['options' => $pSanitize];
                }
                else
                {
                    $filter = $pSanitize;
                    $options = [];
                }

                $data = $this->filter($data, $filter, $options);
            }
        }
        else if($this->_autoSanitization)
        {
            $data = $this->filter($data);
        }

        return $data;
    }
    
    /**
     * @see ParametersInterface::sets()
     */
    public function sets(array $pData) 
    {
        $globals = [
            $GLOBALS,
            $_SERVER,
            $_GET,
            $_POST,
            $_FILES,
            $_COOKIE,
            $_REQUEST,
            $_ENV
        ];
        
        if(isset($_SESSION))
        {
            $globals[] = $_SESSION;
        }
        
        if(in_array($this->_data, $globals, true))
        {
            foreach($this->_data as $key => $value)
            {
                unset($this->_data[$key]);
            }

            foreach($pData as $key => $value)
            {
                $this->_data[$key] = $value;
            } 
        }
        else
        {
            $this->_data = $pData;
        }
    }

    /**
     * @see Parameters::has()
     */
    public function offsetExists($pKey) 
    { 
        return $this->has($pKey);
    } 

    /**
     * @see Parameters::set()
     * @throws \InvalidArgumentException
     */
    public function offsetSet($pKey, $pValue) 
    { 
        if(null === $pKey)
        {
            throw new \InvalidArgumentException('Key parameter cannot be undefined.');
        }
        
        $this->set($pKey, $pValue);
    } 

    /**
     * @see Parameters::get()
     */
    public function offsetGet($pKey) 
    { 
        return $this->get($pKey);
    } 

    /**
     * @see Parameters::remove()
     */
    public function offsetUnset($pKey) 
    { 
        $this->remove($pKey);
    } 
    
    public function rewind() 
    {
        return reset($this->_data);
    }
    
    /**
     * @return mixed
     */
    public function current() 
    {
        return $this->get(key($this->_data));
    }
    
    /**
     * @return string|integer
     */
    public function key() 
    {
        return key($this->_data);
    }
    
    public function next() 
    {
        return next($this->_data);
    }
    
    /**
     * @return boolean
     */
    public function valid() 
    {
        return null !== key($this->_data);
    }
    
    /**
     * @return integer
     */
    public function count()
    {
        return count($this->_data);
    }
    
    /**
     * @see Parameters::has()
     */
    public function __issset($pKey)
    {
        return $this->has($pKey);
    }
    
    /**
     * @see Parameters::get()
     */
    public function __get($pKey)
    {
        return $this->get($pKey);
    }
    
    /**
     * @see Parameters::set()
     */
    public function __set($pKey, $pValue)
    {
        $this->set($pKey, $pValue);
    }
    
    /**
     * @see Parameters::remove()
     */
    public function __unset($pKey)
    {
        $this->remove($pKey);
    }
    
    /**
     * @see ParametersInterface::merge()
     */
    public function merge($pData, $pRecursive = false)
    {
        if($pData instanceof self)
        {
            $pData = $pData->gets(['filter' => false]);
        }
        
        if($pRecursive)
        {
            $this->_data = Arr::merge($this->_data, $pData);
        }
        else
        {
            $this->_data = array_merge($this->_data, $pData);
        }
    }
    
    /**
     * @param mixed $pContent
     * @param string|integer $pSanitize
     * @param array $pOptions
     * @return mixed
     * @throws \RuntimeException
     */
    public function filter($pContent, $pSanitize = 'FILTER_SANITIZE_FULL_SPECIAL_CHARS', array $pOptions = [])
    {
        if(null === $this->_sanitizer)
        {
            throw new \RuntimeException('The Sanitizer class is not defined.');
        }
        
        return $this->_sanitizer->filter($pContent, $pSanitize, $pOptions);
    }
}
