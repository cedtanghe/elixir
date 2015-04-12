<?php

namespace Elixir\Config\Cache;

use Elixir\Config\Loader\LoaderFactory;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class RawCache
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
     * @var array 
     */
    protected $files = []; 
    
    /**
     * @var boolean 
     */
    protected $rebuild = false; 
    
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
        $path = $path ?: 'application' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;
    
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
            $this->rebuild = true;
        }
    }
    
    public function getMetaFile()
    {
        return $this->path . DIRECTORY_SEPARATOR . $this->name . '.meta';
    }
    
    public function getCacheFile()
    {
        return $this->path . DIRECTORY_SEPARATOR . $this->name . '.php';
    }
    
    public function loadFromCache($file, array $options = [])
    {
        $this->files[] = ['file' => $file, 'options' => $options];
        
        if ($this->isFresh($file))
        {
            return $this->cachedata[$file];
        }
        
        $this->rebuild = true;
        
        $loader = LoaderFactory::create($file, $options);
        return $loader->load($file, isset($options['recursive']) ? $options['recursive'] : false);
    }
    
    protected function isFresh($file)
    {
        if(!empty($this->metadata) && isset($this->metadata[$file]))
        {
            return filemtime($file) <= $this->metadata[$file]['last_modified'];
        }
        
        return false;
    }
    
    public function exportCache()
    {
        if ($this->rebuild)
        {
            // Todo
        }
    }
}
