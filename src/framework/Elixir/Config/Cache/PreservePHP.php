<?php

namespace Elixir\Config\Cache;

use Elixir\Config\Cache\CacheableInterface;
use Elixir\Config\ConfigInterface;
use Elixir\Config\Loader\LoaderFactory;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class PreservePHP implements CacheableInterface 
{
    /**
     * @var string 
     */
    protected $debug = true;
    
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
     * @var boolean 
     */
    protected $loaded = false;

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
     * @param boolean $debug
     */
    public function __construct($path = null, $name = 'cache', $debug = true) 
    {
        define('ELIXIR_CONFIG_CACHE', true);
        
        $path = $path ? : 'application' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;
        
        if (!is_dir($path)) 
        {
            mkdir($path, 0777, true);
        }

        $this->path = rtrim($path, DIRECTORY_SEPARATOR);
        $this->name = $name;
        $this->debug = $debug;
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
        
        if (file_exists($this->getMetaFile()) && file_exists($this->getCacheFile()))
        {
            $this->metadata = unserialize(file_get_contents($this->getMetaFile()));
            $this->cachedata = include $this->getCacheFile();
            
            if($this->metadata['_type'] != __CLASS__)
            {
                $this->metadata = null;
                $this->cachedata = null;
                $this->build = true;
                
                return false;
            }
            
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
        
        if ($this->isFresh($file)) 
        {
            if (strstr($file, '.php'))
            {
                $loader = LoaderFactory::create($file, $options);
                $data = $loader->load($this->cachedata[md5($file)], $options['recursive']);
            }
            else
            {
                $data = $this->cachedata[md5($file)];
            }
        }
        else
        {
            $this->build = true;
            
            $loader = LoaderFactory::create($file, $options);
            $data = $loader->load($file, $options['recursive']);
        }
        
        $this->injectToCache($file, $data);
        return $data;
    }
    
    /**
     * @param string $file
     * @param array $data
     */
    protected function injectToCache($file, array $data)
    {
        if (strstr($file, '.php'))
        {
            // Closure and var_export (or serialization, etc.) are not friends.
            $data = preg_replace('/^(<\?php\s+return)|(;\s*?(\?>)?[\n\r\t]*)$/i', '', file_get_contents($file));
        }
        else
        {
            $data = var_export($data, true);
        }
        
        $this->files[$file] = $data;
    }

    /**
     * @param string $file
     * @return boolean
     */
    protected function isFresh($file)
    {
        if(!$this->debug && $this->cacheLoaded())
        {
            return true;
        }
        
        $crypted = md5($file);
        
        if (!empty($this->metadata) && isset($this->metadata[$crypted]))
        {
            return filemtime($file) <= $this->metadata[$crypted];
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
            $metadata = ['_type' => __CLASS__];
            $cachedata = "<?php \n\n";
            $cachedata .= 'if (!defined("ELIXIR_CONFIG_CACHE")) { header("HTTP/1.0 403 Forbidden"); exit(); }';
            $cachedata .= "\n\nreturn [\n";
            
            foreach ($this->files as $file => $data)
            {
                $metadata[md5($file)] = filemtime($file);
                $cachedata .= sprintf('\'%s\' => %s,', md5($file), $data) . "\n";
            }
            
            $cachedata .= "];";
            
            $writed = file_put_contents(
                $this->getMetaFile(), 
                serialize($metadata), 
                LOCK_EX
            );
            
            if (false === $writed)
            {
                return false;
            }
            
            $writed = file_put_contents(
                $this->getCacheFile(), 
                $cachedata, 
                LOCK_EX
            );
            
            if (false === $writed)
            {
                return false;
            }
            
            return true;
        }
        
        return false;
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
