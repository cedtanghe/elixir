<?php

namespace Elixir\Config\Cache;

use Elixir\Config\Cache\CacheableInterface;
use Elixir\Config\ConfigInterface;
use Elixir\Config\Loader\LoaderFactory;
use Elixir\Config\Writer\WriterFactory;
use Elixir\Config\Writer\WriterInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class Compiled implements CacheableInterface 
{
    /**
     * @var string|numeric|null
     */
    protected $cacheVersion = null;
    
    /**
     * @var string 
     */
    protected $path;

    /**
     * @var string 
     */
    protected $file;

    /**
     * @var ConfigInterface 
     */
    protected $config;

    /**
     * @var boolean 
     */
    protected $build = false;
    
    /**
     * @var boolean 
     */
    protected $loaded = false;

    /**
     * @var WriterInterface 
     */
    protected $writer;

    /**
     * @var array 
     */
    protected $cachedata;

    /**
     * @param string $path
     * @param string $file
     * @param string|numeric|null $cacheVersion
     */
    public function __construct($path = null, $file = 'cache.php', $cacheVersion = null) 
    {
        $path = $path ? : 'application' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;
        
        if (!is_dir($path)) 
        {
            mkdir($path, 0777, true);
        }

        $this->path = rtrim($path, DIRECTORY_SEPARATOR);
        $this->file = $file;
        $this->cacheVersion = $cacheVersion;
    }
    
    /**
     * @see CacheableInterface::setCacheVersion()
     */
    public function setCacheVersion($value)
    {
        $this->cacheVersion = $value;
    }
    
    /**
     * @return string|numeric|null
     */
    public function getCacheVersion()
    {
        return $this->cacheVersion;
    }
    
    /**
     * @param WriterInterface $value
     */
    public function setWriter(WriterInterface $value)
    {
        $this->writer = $value;
    }
    
    /**
     * @return WriterInterface
     */
    public function getWriter()
    {
        return $this->writer;
    }

    /**
     * @see CacheableInterface::setConfig()
     */
    public function setConfig(ConfigInterface $value) 
    {
        $this->config = $value;
    }
    
    /**
     * @see CacheableInterface::loadCache()
     */
    public function loadCache()
    {
        if ($this->loaded)
        {
            return $this->cacheLoaded();
        }
        
        $this->loaded = true;
        
        if (file_exists($this->getCacheFile()))
        {
            $loader = LoaderFactory::create($this->getCacheFile());
            $this->cachedata = $loader->load($this->getCacheFile());
            
            if (isset($this->cachedata['_version']) && $this->cachedata['_version'] !== $this->cacheVersion)
            {
                $this->cachedata = null;
                $this->build = true;
                
                return false;
            }
            
            $this->config->sets($this->cachedata);
            return true;
        } 
        else
        {
            $this->build = true;
            return false;
        }
    }
    
    /**
     * @see CacheableInterface::cacheLoaded()
     */
    public function cacheLoaded()
    {
        if (!$this->loaded)
        {
            $this->loadCache();
        }
        
        return null !== $this->cachedata;
    }

    /**
     * @see CacheableInterface::loadFromCache()
     */
    public function loadFromCache($file, array $options = []) 
    {
        $this->loadCache();
        
        if($this->cacheLoaded())
        {
            return [];
        }
        
        return false;
    }
    
    /**
     * @see CacheableInterface::exportToCache()
     */
    public function exportToCache() 
    {
        if ($this->build)
        {
            $this->config->set('_version', $this->cacheVersion);
            
            if (null === $this->writer)
            {
                $this->writer = WriterFactory::create($this->getCacheFile());
            }
            
            $this->writer->setConfig($this->config);
            $writed = $this->writer->export($this->getCacheFile());
            
            $this->config->remove('_version');
            return $writed;
        }
        
        return false;
    }
    
    /**
     * @return string
     */
    protected function getCacheFile() 
    {
        return $this->path . DIRECTORY_SEPARATOR . $this->file;
    }
}
