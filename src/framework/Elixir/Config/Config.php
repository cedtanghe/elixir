<?php

namespace Elixir\Config;

use Elixir\Config\ConfigInterface;
use Elixir\Config\Loader\LoaderFactory;
use Elixir\Config\Processor\ProcessorTrait;
use Elixir\Config\Writer\WriterInterface;
use Elixir\Util\Arr;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class Config implements ConfigInterface, \ArrayAccess, \Iterator, \Countable 
{
    use ProcessorTrait;
    
    /**
     * @var string 
     */
    protected $environment;

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
     * @param mixed $config
     * @param array $options
     */
    public function load($config, array $options = [])
    {
        $recursive = isset($options['recursive']) ? $options['recursive'] : false;

        if ($config instanceof self)
        {
            $this->merge($config, $recursive);
        } 
        else 
        {
            $options['environment'] = $this->environment;
            
            foreach ((array)$config as $config) 
            {
                $loader = LoaderFactory::create($config, $options);
                $data = $loader->load($config, $recursive);
                
                $this->merge($data, $recursive);
            }
        }
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
        return $this->process(Arr::get($key, $this->data, $default));
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
    public function remove($key) {
        Arr::remove($key, $this->data);
    }

    /**
     * @see ConfigInterface::gets()
     */
    public function gets() 
    {
        return $this->process($this->data);
    }

    /**
     * @see ConfigInterface::sets()
     */
    public function sets(array $data) 
    {
        $this->data = $data;
    }

    /**
     * @see \ArrayAccess::offsetExists()
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * @see \ArrayAccess::offsetSet()
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
     * @see \ArrayAccess::offsetGet()
     */
    public function offsetGet($key) 
    {
        return $this->get($key);
    }

    /**
     * @see \ArrayAccess::offsetUnset()
     */
    public function offsetUnset($key)
    {
        $this->remove($key);
    }

    /**
     * @see \Iterator::rewind() 
     */
    public function rewind() 
    {
        return reset($this->data);
    }

    /**
     * @see \Iterator::current() 
     */
    public function current() 
    {
        return $this->get(key($this->data));
    }

    /**
     * @see \Iterator::key() 
     */
    public function key() 
    {
        return key($this->data);
    }

    /**
     * @see \Iterator::next() 
     */
    public function next()
    {
        return next($this->data);
    }

    /**
     * @see \Iterator::valid() 
     */
    public function valid() 
    {
        return null !== key($this->data);
    }

    /**
     * @see \Countable::count()
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * @see Config::has();
     */
    public function __issset($key) 
    {
        return $this->has($key);
    }

    /**
     * @see Config::get();
     */
    public function __get($key) 
    {
        return $this->get($key);
    }

    /**
     * @see Config::set();
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * @see Config::remove();
     */
    public function __unset($key) 
    {
        $this->remove($key);
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

        if ($recursive) 
        {
            $this->data = array_merge_recursive($this->data, $data);
        } 
        else
        {
            $this->data = array_merge($this->data, $data);
        }
    }
}
