<?php

namespace Elixir\Config\Cache;

use Elixir\Config\Cache\CacheableInterface;
use Elixir\Config\ConfigInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class PreservePHP implements CacheableInterface 
{
    /**
     * @var string 
     */
    protected $path;

    /**
     * @var string 
     */
    protected $name;

    /**
     * @var ConfigInterface 
     */
    protected $config;

    /**
     * @var array 
     */
    protected $files = [];

    /**
     * @var boolean 
     */
    protected $build = false;

    /**
     * @var array 
     */
    protected $metadata;

    /**
     * @var array 
     */
    protected $cachedata;

    /**
     * @param string $path
     * @param string $name
     */
    public function __construct($path = null, $name = 'config') 
    {
        $path = $path ? : 'application' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;

        if (!is_dir($path)) 
        {
            mkdir($path, 0777, true);
        }

        $this->path = rtrim($path, DIRECTORY_SEPARATOR);
        $this->name = $name;

        if (file_exists($this->getMetaFile()) && file_exists($this->getCacheFile()))
        {
            $this->metadata = unserialize(file_get_contents($this->getMetaFile()));
            $this->cachedata = include $this->getCacheFile();
        } 
        else
        {
            $this->build = true;
        }
    }

    /**
     * @see CacheableInterface::setConfig()
     */
    public function setConfig(ConfigInterface $value) 
    {
        $this->config = $value;
    }

    /**
     * @see CacheableInterface::loadFromCache()
     */
    public function loadFromCache($file) 
    {
        $this->files[] = $file;

        if ($this->isFresh($file)) 
        {
            return $this->cachedata[$file];
        }

        $this->build = true;
        return false;
    }

    /**
     * @param string $file
     * @return boolean
     */
    protected function isFresh($file)
    {
        if (!empty($this->metadata) && isset($this->metadata[$file]))
        {
            return filemtime($file) <= $this->metadata[$file]['last_modified'];
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
            // Todo
        }
    }
    
    /**
     * @return string
     */
    protected function getMetaFile()
    {
        return $this->path . DIRECTORY_SEPARATOR . $this->name . '.meta';
    }

    /**
     * @return string
     */
    protected function getCacheFile() 
    {
        return $this->path . DIRECTORY_SEPARATOR . $this->name . '.php';
    }
}
