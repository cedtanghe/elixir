<?php

namespace Elixir\Security;

use Elixir\Util\Str;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Hash
{
    /**
     * @var array
     */
    protected static $_supportedAlgorithms;
    
    /**
     * @param integer $pType
     * @param array $pConfig
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function generateSalt($pType = CRYPT_BLOWFISH, array $pConfig = [])
    {
        switch($pType)
        {
            case CRYPT_BLOWFISH:
                $config = array_merge(['cost' => 14], $pConfig);
                $cost = $config['cost'];
                
                if($cost < 4 || $cost > 31)
                {
                    throw new \InvalidArgumentException('Cost parameter must be in range 04-31.');
                }
                
                if(function_exists('openssl_random_pseudo_bytes'))
                {
                    $salt = openssl_random_pseudo_bytes(16);
                }
                else
                {
                    $salt = Str::random(22);
                }
                
                $encoded = substr(str_replace('+', '.', base64_encode($salt)), 0, 22);
                $prefix = version_compare(PHP_VERSION, '5.3.7') >= 0 ? '$2y$' : '$2a$';
                
                return $prefix . sprintf('%1$02d', $cost) . '$' . $encoded;
            break;
        }
        
        throw new \InvalidArgumentException(sprintf('This type of algorithm is not implemented : %d.', $pType));
    }
    
    /**
     * @param string $pAlgorithm
     * @param string $pStr
     * @param boolean $pRaw
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function hash($pAlgorithm, $pStr, $pRaw = false)
    {
        if(null === static::$_supportedAlgorithms)
        {
            static::$_supportedAlgorithms = hash_algos();
        }
        
        if(!in_array(strtolower($pAlgorithm), static::$_supportedAlgorithms))
        {
            throw new \InvalidArgumentException(sprintf('%s algorithm is not supported.', $pAlgorithm));
        }
        
        return hash($pAlgorithm, $pStr, $pRaw);
    }

    /**
     * @param string $pPassword
     * @param string $pSalt
     * @return string
     * @throws \RuntimeException
     */
    public static function crypt($pPassword, $pSalt)
    {
        $hash = crypt($pPassword, $pSalt);
        
        if(strlen($hash) < 13)
        {
            throw new \RuntimeException('Error during the encryption.');
        }
        
        return $hash;
    }
    
    /**
     * @param string $pPassword
     * @param string $pHash
     * @return boolean
     */
    public static function verify($pPassword, $pHash)
    {
        return crypt($pPassword, $pHash) === $pHash;
    }
}
