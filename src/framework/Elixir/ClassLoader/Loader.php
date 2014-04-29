<?php

namespace Elixir\ClassLoader;

require_once 'LoaderInterface.php';

use Elixir\Cache\CacheInterface;
use Elixir\ClassLoader\LoaderInterface;
use Elixir\HTTP\Session\SessionInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Loader implements LoaderInterface
{
    /**
     * @var string
     */
    const DEFAULT_CACHE_KEY = '___CACHE_LOADER___';
    
    /**
     * @var array 
     */
    protected $_classes = array();
    
    /**
     * @var array 
     */
    protected $_loaded = array();
    
    /**
     * @var array 
     */
    protected $_prefixs = array();
    
    /**
     * @var array 
     */
    protected $_namespaces = array();
    
    /**
     * @var array 
     */
    protected $_classMap = array();
    
    public function __construct() 
    {
        $basePath = __DIR__ . '/../../../';
        
        $this->addIncludePath($basePath);
        $this->addNamespace('Elixir', $basePath . 'framework/Elixir/');
        $this->addNamespace('Elixir\Module', $basePath . 'modules/');
    }
    
    /**
     * @see LoaderInterface::register()
     */
    public function register()
    {
        spl_autoload_register(array($this, 'loadClass'));
    }

    /**
     * @see LoaderInterface::unregister()
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));
    }
    
    /**
     * @param CacheInterface|SessionInterface $pCache
     * @param string $pKey
     */
    public function loadFromCache($pCache, $pKey = self::DEFAULT_CACHE_KEY)
    {
        $data = $pCache->get($pKey, array()) ?: array();
        
        $this->_classes = array_merge(
            $data,
            $this->_classes
        );
    }
    
    /**
     * @param CacheInterface|SessionInterface $pCache
     * @param string $pKey
     */
    public function exportToCache($pCache, $pKey = self::DEFAULT_CACHE_KEY)
    {
        $pCache->set($pKey, $this->_classes);
    }
    
    /**
     * @param string $pPath
     */
    public function addIncludePath($pPath)
    {
        $paths = $this->getIncludePaths();
        
        if(!in_array($pPath, $paths))
        {
            $paths[] = $pPath;
        }
        
        set_include_path(implode(PATH_SEPARATOR, $paths));
    }
     
    /**
     * @return array 
     */
    public function getIncludePaths()
    {
        return explode(PATH_SEPARATOR, get_include_path());
    }
    
    /**
     * @param string $pClassName
     * @param string $pPath
     * @param boolean $pOverride
     */
    public function map($pClassName, $pPath, $pOverride = true)
    {
        if($pOverride || !isset($this->_classMap[$pClassName]))
        {
            $this->_classMap[$pClassName] = array();
        }
        
        $this->_classMap[$pClassName][] = $pPath;
    }
    
    /**
     * @return array
     */
    public function getClassMap()
    {
        return $this->_classMap;
    }
    
    /**
     * @param array $pData
     */
    public function setClassMap(array $pData)
    {
        $this->_classMap = array();
        
        foreach($pData as $className => $paths)
        {
            foreach((array)$paths as $path)
            {
                $this->map($className, $path);
            }
        }
    }

    /**
     * @param string $pPrefix
     * @param string $pPath 
     * @param boolean $pOverride 
     */
    public function addPrefix($pPrefix, $pPath, $pOverride = true)
    {
        $pPrefix = rtrim($pPrefix, '_');
        
        if($pOverride || !isset($this->_prefixs[$pPrefix]))
        {
            $this->_prefixs[$pPrefix] = array();
        }
        
        $this->_prefixs[$pPrefix][] = $pPath;
    }
    
    /**
     * @return array
     */
    public function getPrefixs()
    {
        return $this->_prefixs;
    }
    
    /**
     * @param array $pData
     */
    public function setPrefixs(array $pData)
    {
        $this->_prefixs = array();
        
        foreach($pData as $prefix => $paths)
        {
            foreach((array)$paths as $path)
            {
                $this->addPrefix($prefix, $path);
            }
        }
    }
    
    /**
     * @param string $pNamespace
     * @param string $pPath 
     * @param boolean $pOverride 
     */
    public function addNamespace($pNamespace, $pPath, $pOverride = true)
    {
        $pNamespace = rtrim($pNamespace, '\\');
        
        if($pOverride || !isset($this->_namespaces[$pNamespace]))
        {
            $this->_namespaces[$pNamespace] = array();
        }
        
        $this->_namespaces[$pNamespace][] = $pPath;
    }
    
    /**
     * @return array
     */
    public function getNamespaces()
    {
        return $this->_namespaces;
    }
    
    /**
     * @param array $pData
     */
    public function setNamespaces(array $pData)
    {
        $this->_namespaces = array();
        
        foreach($pData as $namespace => $paths)
        {
            foreach((array)$paths as $path)
            {
                $this->addNamespace($namespace, $path);
            }
        }
    }
    
   /**
    * @see LoaderInterface::loadClass()
    */
    public function loadClass($pClassName) 
    {
        if(isset($this->_loaded[$pClassName]))
        {
            return true;
        }
        
        if(isset($this->_classes[$pClassName]))
        {
            $this->_loaded[$pClassName] = true;
            
            require_once $this->_classes[$pClassName];
            return true;
        }
        
        $paths = $this->paths($pClassName);
        
        foreach($paths as $path)
        {
            if($this->find($path))
            {
                $this->_classes[$pClassName] = $path;
                $this->_loaded[$pClassName] = true;

                require_once $path;
                return true;
            }
        }
        
        return false;
    }
    
    /**
    * @see LoaderInterface::classExist()
    */
    public function classExist($pClassName)
    {
        if(isset($this->_loaded[$pClassName]) || isset($this->_classes[$pClassName]))
        {
            return true;
        }
        
        $paths = $this->paths($pClassName);
        
        foreach($paths as $path)
        {
            if($this->find($path))
            {
                $this->_classes[$pClassName] = $path;
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * @param string $pFile
     * @return boolean 
     */
    protected function find($pFile)
    {
        if(file_exists($pFile)) 
        {
            return true;
        }
        
        foreach($this->getIncludePaths() as $path)
        {
            if(file_exists($path . '/' . $pFile)) 
            {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * @param string $pClassName
     * @return array 
     */
    protected function paths($pClassName)
    {
        if(isset($this->_classMap[$pClassName]))
        {
            return $this->_classMap[$pClassName];
        }
        
        $paths = array('');
        $className = $pClassName;
        
        if(false !== strpos($className, self::NAMESPACE_SEPARATOR))
        {
            $type = self::NAMESPACE_SEPARATOR;
        }
        else
        {
            $type = self::PREFIX_SEPARATOR;
        }
        
        $last = '';
        $search = $type == self::NAMESPACE_SEPARATOR ? $this->_namespaces : $this->_prefixs;
        
        foreach($search as $key => $value)
        {
            if(substr($pClassName, 0, strlen($key)) === $key)
            {
                if(strlen($key) > strlen($last))
                {
                    $last = $key;
                    
                    $paths = $value;
                    $className = substr($pClassName, strlen($key) + 1);
                }
            }
        }
        
        $result = array();
        
        foreach($paths as $path)
        {
            $result[] =  $path . str_replace($type , '/', $className) . '.php';
        }
        
        return $result;
    }
}