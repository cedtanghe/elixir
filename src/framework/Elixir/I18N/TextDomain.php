<?php

namespace Elixir\I18N;

use Elixir\I18N\Loader\LoaderFactory;
use Elixir\Util\Arr;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class TextDomain
{
    /**
     * @var array 
     */
    protected $_resources = [];
    
    /**
     * @var array 
     */
    protected $_data = [];
    
    /**
     * @param array $pResources
     * @param array $pData
     */
    public function __construct(array $pResources = [], array $pData = []) 
    {
        $this->setResources($pResources);
        $this->sets($pData);
    }
    
    /**
     * @param mixed $pResource
     * @param string $pLocale
     */
    public function addResource($pResource, $pLocale)
    {
        if (!isset($this->_resources[$pLocale])) 
        {
            $this->_resources[$pLocale] = [];
        }
        
        $this->_resources[$pLocale][] = $pResource;
    }
    
    /**
     * @return array
     */
    public function getResources()
    {
        return $this->_resources;
    }
    
    /**
     * @param array $pData
     */
    public function setResources(array $pData)
    {
        $this->_resources = [];
        
        foreach($pData as $locale => $resources)
        {
            foreach((array)$resources as $resource)
            {
                $this->addResource($resource, $locale);
            }
        }
    }
    
    /**
     * @param mixed $pKey
     * @param string $pLocale
     * @return boolean
     */
    public function has($pKey, $pLocale)
    {
        $this->load($pLocale);
        $key = [$pLocale];
        
        foreach((array)$pKey as $k)
        {
            $key[] = $k;
        }
        
        return Arr::has($key, $this->_data);
        
    }
    
    /**
     * @param mixed $pKey
     * @param mixed $pValue
     * @param string $pLocale
     */
    public function set($pKey, $pValue, $pLocale)
    {
        $this->load($pLocale);
        $key = [$pLocale];
        
        foreach((array)$pKey as $k)
        {
            $key[] = $k;
        }
        
        Arr::set($key, $pValue, $this->_data);
    }
    
    /**
     * @param string $pKey
     * @param string $pLocale
     * @param mixed $pDefault
     * @return mixed
     * @throws \LogicException
     */
    public function get($pKey, $pLocale, $pDefault = null)
    {
        $this->load($pLocale);
        $key = [$pLocale];
        
        foreach((array)$pKey as $k)
        {
            $key[] = $k;
        }
        
        $result = Arr::get($key, $this->_data, null);
        
        if(null !== $result)
        {
            if(!is_string($result))
            {
                throw new \LogicException(sprintf('The expected result should be a string for key "%s".', $pKey));
            }
            
            return $result;
        }
        
        return is_callable($pDefault) ? $pDefault() : $pDefault;
    }
    
    /**
     * @param string $pKey
     * @param string $pLocale
     */
    public function remove($pKey, $pLocale)
    {
        $this->load($pLocale);
        $key = [$pLocale];
        
        foreach((array)$pKey as $k)
        {
            $key[] = $k;
        }
        
        Arr::remove($key, $this->_data);
    }
    
    /**
     * @param boolean $pLoadResources
     * @return array
     */
    public function gets($pLoadResources = false)
    {
        if($pLoadResources)
        {
            foreach(array_keys($this->_resources) as $locale)
            {
                $this->load($locale);
            }
        }

        return $this->_data;
    }
    
    /**
     * @param array $pData
     * @param boolean $pLoadResources
     * @return array|void
     */
    public function sets(array $pData)
    {
        $this->_data = [];
        
        foreach($pData as $locale => $data)
        {
            foreach((array)$data as $key => $value)
            {
                $this->set($key, $value, $locale);
            }
        }
    }

    /**
     * @return boolean
     */
    public function isLoaded($pLocale)
    {
        return !(isset($this->_resources[$pLocale]) && count($this->_resources[$pLocale]) > 0);
    }
    
    /**
     * @param string $pLocale
     */
    public function load($pLocale)
    {
        if(!$this->isLoaded($pLocale))
        {
            $resources = $this->_resources[$pLocale];
            unset($this->_resources[$pLocale]);
            
            foreach($resources as $resource)
            {
                $loader = LoaderFactory::create($resource);
                $data = [$pLocale => $loader->load($resource)];
                
                $this->mergeData($data);
            } 
        }
    }
    
    /**
     * @param array $pResources
     */
    protected function mergeResources(array $pResources)
    {
        $this->setResources(array_merge($this->getResources(), $pResources));
    }
    
    /**
     * @param array $pData
     */
    protected function mergeData(array $pData)
    {
        $this->sets(Arr::merge($this->gets(false), $pData));
    }

    /**
     * @param TextDomain $pData
     */
    public function merge(self $pData)
    {
        $this->mergeResources($pTextDomain->getResources());
        $this->mergeData($pTextDomain->gets(false));
    }
}
