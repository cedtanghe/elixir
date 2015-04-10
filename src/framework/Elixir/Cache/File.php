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
     * @param string $path
     */
    public function __construct($path = null) 
    {
        $path = $path ?: 'application' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
        
        if (!is_dir($path))
        {
            mkdir($path, 0777, true);
        }
        
        $this->path = rtrim($path, DIRECTORY_SEPARATOR);
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
        return $this->path . DIRECTORY_SEPARATOR . md5($key) . '.cache';
    }

    /**
     * @see CacheAbstract::has()
     */
    public function exists($key)
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
            $ttl = (int)trim(fgets($handle));

            if (time() > $ttl) 
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
     * @see CacheAbstract::store()
     */
    public function store($key, $value, $ttl = self::DEFAULT_TTL)
    {
        $ttl = time() + $this->parseTimeToLive($ttl);

        file_put_contents(
            $this->file($key), 
            $ttl . "\n" . $this->getEncoder()->encode($value), 
            LOCK_EX
        );
        
        return true;
    }
    
    /**
     * @see CacheAbstract::delete()
     */
    public function delete($key)
    {
        $file = $this->file($key);

        if (file_exists($file))
        {
            return unlink($file);
        }
        
        return false;
    }
    
    /**
     * @see CacheAbstract::incremente()
     */
    public function incremente($key, $step = 1)
    {
        $file = $this->file($key);

        if (file_exists($file))
        {
            $handle = fopen($file, 'r');
            $ttl = (int)trim(fgets($handle));

            if (time() > $ttl) 
            {
                fclose($handle);
                unlink($file);

                return 0;
            }

            $data = '';

            while (!feof($handle)) 
            {
                $data .= fgets($handle);
            }
            
            fclose($handle);
            
            $data = (int)$this->getEncoder()->decode($data);
            $data += $step;
            
            file_put_contents(
                $this->file($key), 
                $ttl . "\n" . $this->getEncoder()->encode($data), 
                LOCK_EX
            );
            
            return $data;
        }
        
        return 0;
    }
    
    /**
     * @see CacheAbstract::decremente()
     */
    public function decremente($key, $step = 1)
    {
        $file = $this->file($key);

        if (file_exists($file))
        {
            $handle = fopen($file, 'r');
            $ttl = (int)trim(fgets($handle));

            if (time() > $ttl) 
            {
                fclose($handle);
                unlink($file);

                return 0;
            }

            $data = '';

            while (!feof($handle)) 
            {
                $data .= fgets($handle);
            }
            
            fclose($handle);
            
            $data = (int)$this->getEncoder()->decode($data);
            $data -= $step;
            
            file_put_contents(
                $this->file($key), 
                $ttl . "\n" . $this->getEncoder()->encode($data), 
                LOCK_EX
            );
            
            return $data;
        }
        
        return 0;
    }
    
    /**
     * @see CacheAbstract::flush()
     */
    public function flush()
    {
        $files = glob($this->path . DIRECTORY_SEPARATOR . '*.cache');

        foreach ($files as $file)
        {
            unlink($file);
        }
        
        return true;
    }
}
