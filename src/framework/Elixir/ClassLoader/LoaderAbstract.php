<?php

namespace Elixir\ClassLoader;

require_once 'CacheableInterface.php';
require_once 'LoaderInterface.php';

use Elixir\Cache\CacheInterface;
use Elixir\ClassLoader\CacheableInterface;
use Elixir\ClassLoader\LoaderInterface;
use Elixir\HTTP\Session\SessionInterface;
use Elixir\Util\Arr;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
abstract class LoaderAbstract implements LoaderInterface, CacheableInterface
{
    /**
     * @var array 
     */
    protected $classes = [];

    /**
     * @var array 
     */
    protected $loaded = [];

    /**
     * @var array 
     */
    protected $aliases = [];

    /**
     * @var array 
     */
    protected $prefixes = [];
    
    /**
     * @var CacheInterface|SessionInterface 
     */
    protected $cache;
    
    /**
     * @var string|numeric|null
     */
    protected $cacheVersion = null;
    
    /**
     * @var string 
     */
    protected $cacheKey;
    
    /**
     * @return CacheInterface|SessionInterface 
     */
    public function getCache()
    {
        return $this->cache;
    }
    
    /**
     * @return string|numeric|null
     */
    public function getCacheVersion()
    {
        return $this->cacheVersion;
    }
    
    /**
     * @return string
     */
    public function getCacheKey()
    {
        return $this->cacheKey;
    }
    
    /**
     * @see LoaderInterface::register()
     */
    public function register($prepend = false) 
    {
        spl_autoload_register([$this, 'loadClass'], true, $prepend);
    }

    /**
     * @see LoaderInterface::unregister()
     */
    public function unregister() 
    {
        spl_autoload_unregister([$this, 'loadClass']);
    }
    
    /**
     * @see CacheableInterface::loadFromCache()
     */
    public function loadFromCache($cache, $version = null, $key = self::DEFAULT_CACHE_KEY)
    {
        $this->cache = $cache;
        $this->cacheVersion = $version;
        $this->cacheKey = $key;
        
        $data = $this->cache->get($this->cacheKey, []) ?: [];
        $version = Arr::get('version', $data);
        
        if (null === $this->cacheVersion || null === $version || $version === $this->cacheVersion)
        {
            if (null !== $version)
            {
                $this->cacheVersion = $version;
            }
            
            $this->classes = array_merge(
                Arr::get('classes', $data, []),
                $this->classes
            );
            
            return true;
        }
        
        return false;
    }
    
    /**
     * @param string $path
     */
    public function addIncludePath($path)
    {
        $paths = $this->getIncludePaths();

        if (!in_array($path, $paths))
        {
            $paths[] = rtrim($path, '/\\');
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
     * @param string $className
     * @param string $path
     */
    public function map($className, $path)
    {
        $this->classes[ltrim($className, '\\')] = $path;
    }
    
    /**
     * @param string $className
     * @param string $alias
     */
    public function alias($className, $alias) 
    {
        class_alias($className, $alias);
        $this->aliases[ltrim($alias, '\\')] = ltrim($className, '\\');
    }

    /**
     * @param string $prefix
     * @param string $baseDir 
     * @param boolean $prepend 
     */
    public function addNamespace($prefix, $baseDir, $prepend = false)
    {
        $prefix = rtrim(trim($prefix, '\\'), '_');
        $baseDir = rtrim($baseDir, '/\\') . DIRECTORY_SEPARATOR;

        if (!isset($this->prefixes[$prefix])) 
        {
            $this->prefixes[$prefix] = [];
        }

        if ($prepend) 
        {
            array_unshift($this->prefixes[$prefix], $baseDir);
        } 
        else 
        {
            array_push($this->prefixes[$prefix], $baseDir);
        }
    }
    
    /**
    * @see LoaderInterface::classExist()
    */
    public function classExist($className)
    {
        $className = ltrim($className, '\\');
        
        if (isset($this->aliases[$className])) 
        {
            $className = $this->aliases[$className];
        }

        if (isset($this->loaded[$className]))
        {
            return true;
        }

        if (isset($this->classes[$className]))
        {
            return true;
        }

        $paths = $this->paths($className);

        foreach ($paths as $path)
        {
            if ($this->find($path)) 
            {
                $this->classes[$className] = $path;
                return true;
            }
        }

        return false;
    }
    
    /**
     * @see LoaderInterface::findClass()
     */
    public function findClass($className)
    {
        $className = ltrim($className, '\\');
        
        if (isset($this->aliases[$className])) 
        {
            $className = $this->aliases[$className];
        }
        
        if (isset($this->classes[$className]))
        {
            return $this->classes[$className];
        }

        $paths = $this->paths($className);
        
        foreach ($paths as $path)
        {
            if ($this->find($path)) 
            {
                $this->classes[$className] = $path;
                return $path;
            }
        }

        return null;
    }

    /**
     * @see LoaderInterface::loadClass()
     */
    public function loadClass($className) 
    {
        $className = ltrim($className, '\\');
        
        if (isset($this->aliases[$className])) 
        {
            $className = $this->aliases[$className];
        }

        if (isset($this->loaded[$className]))
        {
            return true;
        }

        if (isset($this->classes[$className]))
        {
            $this->loaded[$className] = true;

            require_once $this->classes[$className];
            return true;
        }

        $paths = $this->paths($className);
        
        foreach ($paths as $path)
        {
            if ($this->find($path)) 
            {
                $this->classes[$className] = $path;
                $this->loaded[$className] = true;

                require_once $path;
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $file
     * @return boolean 
     */
    protected function find($file) 
    {
        if (file_exists($file)) 
        {
            return true;
        }

        foreach ($this->getIncludePaths() as $path) 
        {
            if (file_exists(rtrim($path, '/\\') . DIRECTORY_SEPARATOR . $file)) 
            {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $className
     * @return array 
     */
    abstract protected function paths($className);
    
    /**
     * @see CacheableInterface::loadFromCache()
     */
    public function exportToCache()
    {
        if (null !== $this->cache)
        {
            $this->cache->set(
                $this->cacheKey, 
                [
                    'classes' => $this->classes,
                    'version' => $this->cacheVersion
                ]
            );

            return true;
        }
        
        return false;
    }
    
    /**
     * @see CacheableInterface::loadFromCache()
     */
    public function invalidateCache()
    {
        if (null !== $this->cache)
        {
            $this->cache->remove($this->cacheKey);
            return true;
        }
        
        return false;
    }
}
