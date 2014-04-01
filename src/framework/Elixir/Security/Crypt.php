<?php

namespace Elixir\Security;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Crypt
{
    /**
     * @var string
     */
    protected $_cipher;
    
    /**
     * @var string
     */
    protected $_secret;
    
    /**
     * @var string
     */
    protected $_mode;
    
    /**
     * @var integer
     */
    protected $_ivSize;
    
    /**
     * @param string $pSecret
     * @param string $pCipher
     * @param string $pMode
     * @throws \RuntimeException
     */
    public function __construct($pSecret, $pCipher = MCRYPT_RIJNDAEL_128, $pMode = MCRYPT_MODE_CBC) 
    {
        if(!extension_loaded('mcrypt'))
        {
            throw new \RuntimeException('Mcrypt is not available.');
        }
        
        $this->_cipher = $pCipher;
        $this->_mode = $pMode;
        $this->_ivSize = mcrypt_get_iv_size($this->_cipher, $this->_mode);
        $maxSize = mcrypt_get_key_size($this->_cipher, $this->_mode);
        $this->_secret = strlen($pSecret) > $maxSize ? substr($pSecret, 0, $maxSize): $pSecret;
    }
    
    /**
     * @return string
     */
    public function getCipher()
    {
        return $this->_cipher;
    }
    
    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->_secret;
    }
    
    /**
     * @return string
     */
    public function getMode()
    {
        return $this->_mode;
    }
    
    /**
     * @param string $pStr
     * @return string
     */
    public function encrypt($pStr) 
    {
        $iv = mcrypt_create_iv($this->_ivSize, MCRYPT_RAND);
        
        $encripted = mcrypt_encrypt(
            $this->_cipher,
            $this->_secret,
            $pStr,
            $this->_mode,
            $iv
        );
        
        return base64_encode($iv . $encripted);
    }
    
    /**
     * @param string $pStr
     * @return string
     */
    public function decrypt($pStr) 
    {
        $decode = base64_decode($pStr);
        
        return rtrim(
            mcrypt_decrypt(
                $this->_cipher,
                $this->_secret,
                substr($decode, $this->_ivSize),
                $this->_mode,
                substr($decode, 0, $this->_ivSize)
            ),
            "\0"
        );
    }
}