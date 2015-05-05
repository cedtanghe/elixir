<?php

namespace Elixir\Config;

use Elixir\Config\Cache\CacheableInterface;
use Elixir\Config\ConfigInterface;
use Elixir\Config\Loader\LoaderFactory;
use Elixir\Config\Processor\ProcessorInterface;
use Elixir\Config\Writer\WriterInterface;
use Elixir\Util\Arr;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class Config implements ConfigInterface, CacheableInterface, \ArrayAccess, \Iterator, \Countable 
{
    /**
     * @var string 
     */
    protected $environment;
    
    /**
     * @var CacheableInterface 
     */
    protected $cache;
    
    /**
     * @var ProcessorInterface 
     */
    protected $processor;

    /**
     * @var array 
     */
    protected $data = [];
    
    /**
     * @param string $environment
     * @param array $data
     */
    public function __construct($environment = null, array $data = []) 
    {
        $this->environment = $environment;
        $this->data = $data;
    }
    
    /**
     * @param CacheableInterface $value
     */
    public function setCacheStrategy(CacheableInterface $value)
    {
        $this->cache = $value;
        $this->cache->setConfig($this);
    }
    
    /**
     * @return CacheableInterface
     */
    public function getCacheStrategy()
    {
        return $this->cache;
    }
    
    /**
     * @param ProcessorInterface $value
     */
    public function setProcessor(ProcessorInterface $value)
    {
        $this->processor = $value;
    }
    
    /**
     * @return ProcessorInterface
     */
    public function getProcessor()
    {
        return $this->processor;
    }
    
    /**
     * @see CacheableInterface::setConfig()
     * @throws \LogicException
     */
    public function setConfig(ConfigInterface $value) 
    {
        throw new \LogicException('Needless to self inject.');
    }
    
    /**
     * @see CacheableInterface::loadCache()
     */
    public function loadCache()
    {
        if (null === $this->cache)
        {
            return false;
        }
        
        return $this->cache->loadCache();
    }
    
    /**
     * @see CacheableInterface::cacheLoaded()
     */
    public function cacheLoaded()
    {
        if (null === $this->cache)
        {
            return false;
        }
        
        return null !== $this->cache->cacheLoaded();
    }
    
    /**
     * @param mixed $config
     * @param array $options
     */
    public function load($config, array $options = [])
    {
        if ($config instanceof self)
        {
            $this->merge($config, isset($options['recursive']) ? $options['recursive'] : false);
        } 
        else 
        {
            $options['environment'] = $this->environment;
            $options['recursive'] = isset($options['recursive']) ? $options['recursive'] : false;
            
            foreach ((array)$config as $config) 
            {
                $data = false;
                
                if (null !== $this->cache && ($isFile = is_file($config)))
                {
                    $data = $this->loadFromCache($config, $options);
                    
                    if (true === $data)
                    {
                        continue;
                    }
                }
                
                if (false === $data)
                {
                    $loader = LoaderFactory::create($config, $options);
                    $data = $loader->load($config, $options['recursive']);
                }
                
                $this->merge($data, $options['recursive']);
            }
        }
    }

    /**
     * @see CacheableInterface::loadFromCache()
     */
    public function loadFromCache($file, array $options = [])
    {
        if (null === $this->cache)
        {
            return null;
        }
        
        return $this->cache->loadFromCache($file, $options);
    }
    
    /**
     * @param WriterInterface $writer
     * @param string $file
     * @return boolean
     */
    public function export(WriterInterface $writer, $file)
    {
        $writer->setConfig($this);
        return $writer->export($file);
    }
    
    /**
     * @see ConfigInterface::has()
     */
    public function has($key)
    {
        return Arr::has($key, $this->data);
    }

    /**
     * @see ConfigInterface::get()
     */
    public function get($key, $default = null) 
    {
        $data = Arr::get($key, $this->data, $default);
        
        if (null !== $this->processor)
        {
            $data = $this->processor->process($data);
        }
        
        return $data;
    }

    /**
     * @see ConfigInterface::set()
     */
    public function set($key, $value) 
    {
        Arr::set($key, $value, $this->data);
    }

    /**
     * @see ConfigInterface::remove()
     */
    public function remove($key) 
    {
        Arr::remove($key, $this->data);
    }

    /**
     * @see ConfigInterface::gets()
     */
    public function gets() 
    {
        $data = $this->data;
        
        if (null !== $this->processor)
        {
            $data = $this->processor->process($data);
        }
        
        return $data;
    }

    /**
     * @see ConfigInterface::sets()
     */
    public function sets(array $data) 
    {
        $this->data = $data;
    }

    /**
     * @ignore
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * @ignore
     */
    public function offsetSet($key, $value) 
    {
        if (null === $key)
        {
            throw new \InvalidArgumentException('The key can not be undefined.');
        }

        $this->set($key, $value);
    }

    /**
     * @ignore
     */
    public function offsetGet($key) 
    {
        return $this->get($key);
    }

    /**
     * @ignore
     */
    public function offsetUnset($key)
    {
        $this->remove($key);
    }

    /**
     * @ignore
     */
    public function rewind() 
    {
        return reset($this->data);
    }

    /**
     * @ignore
     */
    public function current() 
    {
        return $this->get(key($this->data));
    }

    /**
     * @ignore
     */
    public function key() 
    {
        return key($this->data);
    }

    /**
     * @ignore
     */
    public function next()
    {
        return next($this->data);
    }

    /**
     * @ignore
     */
    public function valid() 
    {
        return null !== key($this->data);
    }

    /**
     * @ignore
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * @ignore
     */
    public function __issset($key) 
    {
        return $this->has($key);
    }

    /**
     * @ignore
     */
    public function __get($key) 
    {
        return $this->get($key);
    }

    /**
     * @ignore
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * @ignore
     */
    public function __unset($key) 
    {
        $this->remove($key);
    }
    
    /**
     * @see CacheableInterface::exportToCache()
     */
    public function exportToCache()
    {
        if (null === $this->cache)
        {
            return false;
        }
        
        return $this->cache->exportToCache();
    }
    
    /**
     * @see CacheableInterface::loadFromCache()
     */
    public function invalidateCache()
    {
        if (null === $this->cache)
        {
            return false;
        }
        
        return $this->cache->exportToCache();
    }

    /**
     * @see ConfigInterface::merge();
     */
    public function merge($data, $recursive = false) 
    {
        if ($data instanceof self) 
        {
            $data = $data->gets();
        }

        $this->data = $recursive ? array_merge_recursive($this->data, $data) : array_merge($this->data, $data);
    }
    
    /**
     * @ignore
     */
    public function __debugInfo()
    {
        return [
            'environment' => $this->environment,
            'data' => $this->data
        ];
    }
}
