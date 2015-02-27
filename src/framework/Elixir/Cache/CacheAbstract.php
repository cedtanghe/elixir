<?php

namespace Elixir\Cache;

use Elixir\Cache\CacheInterface;
use Elixir\Cache\Encoder\EncoderInterface;
use Elixir\Util\Str;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

abstract class CacheAbstract implements CacheInterface
{
    /**
     * @var EncoderInterface 
     */
    protected $encoder;
    
    /**
     * @var string 
     */
    protected $identifier;

    /**
     * @param string $identifier
     */
    public function __construct($identifier)
    {
        $this->identifier = preg_replace(
            '/[^a-z0-9\-]+/i', 
            '-', 
            strtolower(Str::removeAccents($identifier))
        );
    }
    
    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }
    
    /**
     * @param EncoderInterface $value
     */
    public function setEncoder(EncoderInterface $value)
    {
        $this->encoder = $value;
    }
    
    /**
     * @return EncoderInterface
     */
    public function getEncoder()
    {
        return $this->encoder;
    }
    
    /**
     * @see CacheInterface::findOrStore()
     */
    public function findOrStore($key, $value, $TTL = 0)
    {
        $get = $this->get($key, null);

        if (null === $get)
        {
            if (is_callable($value))
            {
                $get = call_user_func($value);
            } 
            else 
            {
                $get = $value;
            }
            
            $this->set($key, $get, $TTL);
        }

        return $get;
    }
    
    /**
     * @param integer|string|\DateTime $TTL
     * @return integer
     */
    public function convertTTL($TTL)
    {
        $default = 31556926;
        
        if (0 == $TTL)
        {
            return $default;
        }
        
        if ($TTL instanceof \DateTime)
        {
            $now = new \DateTime(null, $TTL->getTimezone());
            $TTL = $TTL->getTimestamp() - $now->getTimestamp();
        }
        else if (!is_numeric($TTL))
        {
            $time = strtotime($TTL);
            
            if (false === $time)
            {
                return $default;
            }
            
            $now = new \DateTime();
            $TTL = $time - $now->getTimestamp();
        }
        
        return $TTL;
    }
}
