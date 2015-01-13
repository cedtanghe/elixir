<?php

namespace Elixir\ClassLoader;

if(!class_exists('\Elixir\ClassLoader\LoaderInterface'))
{
    require_once 'LoaderInterface.php';
}

use Elixir\Cache\CacheInterface;
use Elixir\ClassLoader\LoaderInterface;
use Elixir\HTTP\Session\SessionInterface;
use Elixir\Util\Arr;

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
     * @var string|numeric|null
     */
    protected $_cacheVersion = null;
    
    /**
     * @var array 
     */
    protected $_classes = [];
    
    /**
     * @var array 
     */
    protected $_loaded = [];
    
    /**
     * @var array 
     */
    protected $_aliases = [];
    
    /**
     * @var array 
     */
    protected $_prefixes = [];
    
    /**
     * @var array 
     */
    protected $_namespaces = [];
    
    /**
     * @var array 
     */
    protected $_classMap = [];
    
    public function __construct() 
    {
        $basePath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
        
        $this->addIncludePath($basePath);
        $this->addNamespace('Elixir', $basePath . 'framework' . DIRECTORY_SEPARATOR . 'Elixir');
        $this->addNamespace('Elixir\Module', $basePath . 'modules');
    }
    
    /**
     * @see LoaderInterface::register()
     */
    public function register()
    {
        spl_autoload_register([$this, 'loadClass']);
    }

    /**
     * @see LoaderInterface::unregister()
     */
    public function unregister()
    {
        spl_autoload_unregister([$this, 'loadClass']);
    }
    
    /**
     * @param string|numeric|null $pValue
     */
    public function setCacheVersion($pValue)
    {
        $this->_cacheVersion = $pValue;
    }
    
    /**
     * @return string|numeric|null
     */
    public function getCacheVersion()
    {
        return $this->_cacheVersion;
    }
    
    /**
     * @param CacheInterface|SessionInterface $pCache
     * @param string $pKey
     */
    public function loadFromCache($pCache, $pKey = self::DEFAULT_CACHE_KEY)
    {
        $data = $pCache->get($pKey, []) ?: [];
        $version = Arr::get('cache-version', $data);
        
        if(null === $this->_cacheVersion || null === $version || $version === $this->_cacheVersion)
        {
            $this->_classes = array_merge(
                Arr::get('classes', $data, []),
                $this->_classes
            );
        }
    }
    
    /**
     * @param CacheInterface|SessionInterface $pCache
     * @param string $pKey
     */
    public function exportToCache($pCache, $pKey = self::DEFAULT_CACHE_KEY)
    {
        $pCache->set(
            $pKey, 
            [
                'classes' => $this->_classes,
                'cache-version' => $this->_cacheVersion
            ]
        );
    }
    
    /**
     * @param string $pPath
     */
    public function addIncludePath($pPath)
    {
        $paths = $this->getIncludePaths();
        
        if(!in_array($pPath, $paths))
        {
            $paths[] = rtrim($pPath, DIRECTORY_SEPARATOR);
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
            $this->_classMap[$pClassName] = [];
        }
        
        $this->_classMap[$pClassName][] = rtrim($pPath, DIRECTORY_SEPARATOR);
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
        $this->_classMap = [];
        
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
        
        if($pOverride || !isset($this->_prefixes[$pPrefix]))
        {
            $this->_prefixes[$pPrefix] = [];
        }
        
        $this->_prefixes[$pPrefix][] = rtrim($pPath, DIRECTORY_SEPARATOR);
    }
    
    /**
     * @return array
     */
    public function getPrefixes()
    {
        return $this->_prefixes;
    }
    
    /**
     * @param array $pData
     */
    public function setPrefixes(array $pData)
    {
        $this->_prefixes = [];
        
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
            $this->_namespaces[$pNamespace] = [];
        }
        
        $this->_namespaces[$pNamespace][] = rtrim($pPath, DIRECTORY_SEPARATOR);
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
        $this->_namespaces = [];
        
        foreach($pData as $namespace => $paths)
        {
            foreach((array)$paths as $path)
            {
                $this->addNamespace($namespace, $path);
            }
        }
    }
    
    /**
     * @param string $pAlias
     * @return boolean
     */
    public function hasAlias($pAlias)
    {
        if(isset($this->_aliases[$pAlias]))
        {
            return true;
        }
        
        return false;
    }
    
    /**
     * @param string $pAlias
     * @param string $pClassName
     */
    public function addAlias($pAlias, $pClassName)
    {
        class_alias($pClassName, $pAlias);
        $this->_aliases[$pAlias] = ltrim($pClassName, '_\\');
    }
    
    /**
     * @param string $pAlias
     * @return string
     */
    public function getClassAlias($pAlias)
    {
        if(isset($this->_aliases[$pAlias]))
        {
            return $this->_aliases[$pAlias];
        }
        
        return $pAlias;
    }
    
   /**
    * @see LoaderInterface::loadClass()
    */
    public function loadClass($pClassName) 
    {
        if($this->hasAlias($pClassName))
        {
            $pClassName = $this->getClassAlias($pClassName);
        }
        
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
        if($this->hasAlias($pClassName))
        {
            $pClassName = $this->getClassAlias($pClassName);
        }
        
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
            if(file_exists(rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $pFile)) 
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
        
        $paths = [''];
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
        $search = $type == self::NAMESPACE_SEPARATOR ? $this->_namespaces : $this->_prefixes;
        
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
        
        $result = [];
        
        foreach($paths as $path)
        {
            $result[] =  $path . DIRECTORY_SEPARATOR . str_replace($type , DIRECTORY_SEPARATOR, $className) . '.php';
        }
        
        return $result;
    }
}
