<?php

namespace Elixir\Config;

use Elixir\Config\ConfigInterface;
use Elixir\Config\Loader\LoaderFactory;
use Elixir\Config\Processor\ProcessorInterface;
use Elixir\Config\Writer\WriterInterface;
use Elixir\Util\Arr;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Config implements ConfigInterface, \ArrayAccess, \Iterator, \Countable
{
    /**
     * @var string 
     */
    protected $_environment;
    
    /**
     * @var array 
     */
    protected $_parameters;
    
    /**
     * @var array
     */
    protected $_processors = [];
    
    /**
     * @param string $pEnvironment
     */
    public function __construct($pEnvironment = null, array $pParameters = []) 
    {
        $this->_environment = $pEnvironment;
        $this->sets($pParameters);
    }
    
    /**
     * @param mixed $pConfig
     * @param array $pOptions
     */
    public function load($pConfig, array $pOptions = [])
    {
        $recursive = isset($pOptions['recursive']) ? $pOptions['recursive'] : false;
        
        if($pConfig instanceof self)
        {
            $this->merge($pConfig, $recursive);
        }
        else
        {
            $pOptions['environment'] = isset($pOptions['environment']) ? $pOptions['environment'] : $this->_environment;
            $pOptions['strict'] = isset($pOptions['strict']) ? $pOptions['strict'] : false;
            
            foreach((array)$pConfig as $config)
            {
                $loader = LoaderFactory::create($config, $pOptions);
                $data = $loader->load($config, $recursive);
                
                $this->merge($data, $recursive);
            }
        }
    }
    
    /**
     * @param WriterInterface $pWriter
     * @param string $pFile
     * @return boolean
     */
    public function export(WriterInterface $pWriter, $pFile)
    {
        $pWriter->setConfig($this);
        return $pWriter->export($pFile);
    }

    /**
     * @param ProcessorInterface $pProcessor
     */
    public function addProcessor(ProcessorInterface $pProcessor)
    {
        $this->_processors[] = $pProcessor;
    }
    
    /**
     * @return array
     */
    public function getProcessors()
    {
        return $this->_processors;
    }
    
    /**
     * @param array $pData
     */
    public function setProcessors(array $pData)
    {
        $this->_processors = [];
        
        foreach($pData as $processor)
        {
            $this->addProcessor($processor);
        }
    }
    
    /**
     * @param mixed $pValue
     * @return $mixed;
     */
    protected function processValue($pValue)
    {
        $value = $pValue;
        
        foreach($this->_processors as $processor)
        {
            $value = $processor->process($value);
        }
        
        return $value;
    }

    /**
     * @param mixed $pKey
     */
    public function has($pKey)
    {
        return Arr::has($pKey, $this->_parameters);
    }
    
    /**
     * @param mixed $pKey
     * @param mixed $pDefault
     * @return mixed
     */
    public function get($pKey, $pDefault = null)
    {
        return $this->processValue(Arr::get($pKey, $this->_parameters, $pDefault));
    }
    
    /**
     * @param mixed $pKey
     * @param mixed $pValue
     */
    public function set($pKey, $pValue)
    {
        Arr::set($pKey, $pValue, $this->_parameters);
    }
    
    /**
     * @param mixed $pKey
     */
    public function remove($pKey)
    {
        Arr::remove($pKey, $this->_data);
    }
    
    /**
     * @return array
     */
    public function gets()
    {
        return $this->processValue($this->_parameters);
    }
    
    /**
     * @param array $pData
     */
    public function sets(array $pData)
    {
        $this->_parameters = $pData;
    }
    
    /**
     * @see Config::has()
     */
    public function offsetExists($pKey) 
    { 
        return $this->has($pKey);
    } 

    /**
     * @see Config::set()
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
     * @see Config::get()
     */
    public function offsetGet($pKey) 
    { 
        return $this->get($pKey);
    } 

    /**
     * @see Config::remove()
     */
    public function offsetUnset($pKey) 
    { 
        $this->remove($pKey);
    } 
    
    public function rewind() 
    {
        reset($this->_parameters);
    }
    
    /**
     * @return mixed
     */
    public function current() 
    {
        return $this->get(key($this->_parameters));
    }
    
    /**
     * @return string|integer
     */
    public function key() 
    {
        return key($this->_parameters);
    }
    
    public function next() 
    {
        next($this->_parameters);
    }
    
    /**
     * @return boolean
     */
    public function valid() 
    {
        return null !== key($this->_parameters);
    }
    
    /**
     * @return integer
     */
    public function count()
    {
        return count($this->_parameters);
    }
    
    /**
     * @see Config::has();
     */
    public function __issset($pKey)
    {
        return $this->has($pKey);
    }
    
    /**
     * @see Config::get();
     */
    public function __get($pKey)
    {
        return $this->get($pKey);
    }
    
    /**
     * @see Config::set();
     */
    public function __set($pKey, $pValue)
    {
        $this->set($pKey, $pValue);
    }
    
    /**
     * @see Config::remove();
     */
    public function __unset($pKey)
    {
        $this->remove($pKey);
    }
    
    /**
     * @see Config::merge();
     */
    public function merge($pData, $pRecursive = false)
    {
        if($pData instanceof self)
        {
            $pData = $pData->gets();
        }
        
        if($pRecursive)
        {
            $this->_parameters = Arr::merge($this->_parameters, $pData);
        }
        else
        {
            $this->_parameters = array_merge($this->_parameters, $pData);
        }
    }
}
