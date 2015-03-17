<?php

namespace Elixir\Cache;

use Elixir\Cache\CacheAbstract;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class File extends CacheAbstract
{
    /**
     * @var string
     */
    const DEFAULT_ENCODER = '\Elixir\Cache\Encoder\Serialize';
    
    /**
     * @var string 
     */
    protected $path;
    
    /**
     * @see CacheAbstract::__construct()
     * @param string $path
     */
    public function __construct($identifier, $path = null) 
    {
        $path = $path ?: 'application' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
        
        if (!is_dir($path))
        {
            mkdir($path, 0777, true);
        }
        
        $this->path = rtrim($path, DIRECTORY_SEPARATOR);
        parent::__construct($identifier);
    }
    
    /**
     * @see CacheAbstract::getEncoder()
     */
    public function getEncoder() 
    {
        if (null === $this->encoder) 
        {
            $class = self::DEFAULT_ENCODER;
            $this->setEncoder(new $class());
        }

        return parent::getEncoder();
    }

    /**
     * @param string $key
     * @return string
     */
    protected function file($key)
    {
        return $this->path . DIRECTORY_SEPARATOR . $this->identifier .  md5($key) . '.cache';
    }

    /**
     * @see CacheAbstract::has()
     */
    public function has($key)
    {
        $file = $this->file($key);
        
        if (file_exists($file))
        {
            $handle = fopen($file, 'r');
            $expired = time() > (int)trim(fgets($handle));
            
            fclose($handle);
            
            if ($expired)
            {
                unlink($file);
            }
            
            return !$expired;
        }
        
        return false;
    }
    
    /**
     * @see CacheAbstract::get()
     */
    public function get($key, $default = null)
    {
        $file = $this->file($key);

        if (file_exists($file))
        {
            $handle = fopen($file, 'r');
            $expired = time() > (int) trim(fgets($handle));

            if ($expired) 
            {
                fclose($handle);
                unlink($file);

                return is_callable($default) ? call_user_func($default) : $default;
            }

            $data = '';

            while (!feof($handle)) 
            {
                $data .= fgets($handle);
            }
            
            fclose($handle);
            return $this->getEncoder()->decode($data);
        }

        return is_callable($default) ? call_user_func($default) : $default;
    }
    
    /**
     * @see CacheAbstract::set()
     */
    public function set($key, $value, $TTL = 0)
    {
        $TTL = time() + $this->convertTTL($TTL);
        $data = $TTL . "\n" . $this->getEncoder()->encode($value);

        file_put_contents($this->file($key), $data, LOCK_EX);
    }
    
    /**
     * @see CacheAbstract::remove()
     */
    public function remove($key)
    {
        $file = $this->file($key);

        if (file_exists($file))
        {
            unlink($file);
        }
    }
    
    /**
     * @see CacheAbstract::has()
     */
    public function clear()
    {
        $files = glob($this->path . DIRECTORY_SEPARATOR . $this->identifier . '*.cache');

        foreach ($files as $file)
        {
            unlink($file);
        }
    }
}
