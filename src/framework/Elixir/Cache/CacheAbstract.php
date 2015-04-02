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
     * @see CacheInterface::remember()
     */
    public function remember($key, $value, $ttl = self::DEFAULT_TTL)
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
            
            $this->set($key, $get, $ttl);
        }

        return $get;
    }
    
    /**
     * @param integer|string|\DateTime $ttl
     * @return integer
     */
    public function parseTimeToLive($ttl = self::DEFAULT_TTL)
    {
        if (0 == $ttl)
        {
            return self::DEFAULT_TTL;
        }
        
        if ($ttl instanceof \DateTime)
        {
            $now = new \DateTime(null, $ttl->getTimezone());
            $ttl = $ttl->getTimestamp() - $now->getTimestamp();
        }
        else if (!is_numeric($ttl))
        {
            $time = strtotime($ttl);
            
            if (false === $time)
            {
                return self::DEFAULT_TTL;
            }
            
            $now = new \DateTime();
            $ttl = $time - $now->getTimestamp();
        }
        
        return $ttl;
    }
}
