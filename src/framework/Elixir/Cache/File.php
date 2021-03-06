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
    protected $_path;

    /**
     * @see CacheAbstract::__construct()
     * @param string $pPath
     */
    public function __construct($pIdentifier, $pPath = 'application/cache/') 
    {
        if(!is_dir($pPath))
        {
            @mkdir($pPath, 0777, true);
        }
        
        $this->_path = rtrim($pPath, '/');
        parent::__construct($pIdentifier);
    }
    
    /**
     * @see CacheAbstract::getEncoder()
     */
    public function getEncoder() 
    {
        if(null === $this->_encoder)
        {
            $class = self::DEFAULT_ENCODER;
            $this->setEncoder(new $class());
        }
        
        return parent::getEncoder();
    }

    /**
     * @param string $pKey
     * @return string
     */
    protected function file($pKey)
    {
        return $this->_path . '/' . $this->_identifier .  md5($pKey) . '.cache';
    }

    /**
     * @see CacheInterface::has()
     */
    public function has($pKey)
    {
        $file = $this->file($pKey);
        
        if(file_exists($file))
        {
            $handle = fopen($file, 'r');
            $expired = time() > (int)trim(fgets($handle));
            
            fclose($handle);
            
            if($expired)
            {
                unlink($file);
            }
            
            return !$expired;
        }
        
        return false;
    }
    
    /**
     * @see CacheInterface::get()
     */
    public function get($pKey, $pDefault = null)
    {
       $file = $this->file($pKey);
        
        if(file_exists($file))
        {
            $handle = fopen($file, 'r');
            $expired = time() > (int)trim(fgets($handle));
            
            if($expired)
            {
                fclose($handle);
                unlink($file);
                
                return is_callable($pDefault) ? $pDefault() : $pDefault;
            }
            
            $data = '';
            
            while(!feof($handle))
            {
                $data .= fgets($handle);
            }
            
            fclose($handle);
            return $this->getEncoder()->decode($data);
        }
        
        return is_callable($pDefault) ? $pDefault() : $pDefault;
    }
    
    /**
     * @see CacheInterface::set()
     */
    public function set($pKey, $pValue, $pTTL = 0)
    {
        $pTTL = time() + $this->convertTTL($pTTL);
        $data = $pTTL . "\n" . $this->getEncoder()->encode($pValue);
        
        file_put_contents($this->file($pKey), $data, LOCK_EX);
    }
    
    /**
     * @see CacheInterface::remove()
     */
    public function remove($pKey)
    {
        $file = $this->file($pKey);
        
        if(file_exists($file))
        {
            unlink($file);
        }
    }
    
    /**
     * @see CacheInterface::has()
     */
    public function clear()
    {
        $files = glob($this->_path . '/' . $this->_identifier . '*.cache');
        
        foreach($files as $file)
        {
            unlink($file);
        }
    }
}

