<?php

namespace Elixir\Cache;

use Elixir\Cache\Encoder\EncoderInterface;
use Elixir\Util\Str;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

abstract class CacheAbstract implements CacheInterface
{
    /**
     * @var EncoderInterface 
     */
    protected $_encoder;
    
    /**
     * @var string 
     */
    protected $_identifier;

    /**
     * @param string $pIdentifier
     */
    public function __construct($pIdentifier)
    {
        $this->_identifier = preg_replace('/[^a-z0-9\-]+/i', '-', strtolower(Str::removeAccents($pIdentifier)));
    }
    
    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->_identifier;
    }
    
    /**
     * @param EncoderInterface $pValue
     */
    public function setEncoder(EncoderInterface $pValue)
    {
        $this->_encoder = $pValue;
    }
    
    /**
     * @return EncoderInterface
     */
    public function getEncoder()
    {
        return $this->_encoder;
    }

    /**
     * @param integer|string|\DateTime $pTTL
     * @param integer $pDefault
     * @return integer
     */
    public function convertTTL($pTTL, $pDefault = 31556926)
    {
        if(0 == $pTTL)
        {
            return $pDefault;
        }
        
        if($pTTL instanceof \DateTime)
        {
            $now = new \DateTime(null, $pTTL->getTimezone());
            $pTTL = $pTTL->getTimestamp() - $now->getTimestamp();
        }
        else if(!is_numeric($pTTL))
        {
            $time = strtotime($pTTL);
            
            if(false === $time)
            {
                return $pDefault;
            }
            
            $now = new \DateTime();
            $pTTL = $time - $now->getTimestamp();
        }
        
        return $pTTL;
    }
}
