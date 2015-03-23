<?php

namespace Elixir\Security\Authentification;

use Elixir\Security\Authentification\Identity;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Result
{
    /**
     * @var integer
     */
    const SUCCESS = 1;
    
    /**
     * @var integer
     */
    const FAILURE = 2;
    
    /**
     * @var integer
     */
    const IDENTITY_NOT_FOUND = 4;
    
    /**
     * @var integer
     */
    const CREDENTIAL_INVALID = 8;
    
    /**
     * @var integer
     */
    const UNKNOWN = 16;
    
    /**
     * @var integer
     */
    protected $_code;
    
    /**
     * @var Identity
     */
    protected $_identity;
    
    /**
     * @var array
     */
    protected $_messages;
    
    /**
     * @param integer $pCode
     * @param Identity $pIdentity
     * @param array $pMessages
     */
    public function __construct($pCode, Identity $pIdentity = null, array $pMessages = []) 
    {
        $this->_code = $pCode;
        $this->_identity = $pIdentity;
        $this->_messages = $pMessages;
    }
    
    /**
     * @return boolean
     */
    public function isSuccess()
    {
        return $this->hasCode(self::SUCCESS);
    }
    
    /**
     * @return boolean
     */
    public function isFailure()
    {
        return !$this->isSuccess();
    }

    /**
     * @return integer
     */
    public function getCode()
    {
        return $this->_code;
    }
    
    /**
     * @var integer
     * @return boolean
     */
    public function hasCode($pCode)
    {
        return ($this->_code & $pCode) == $pCode;
    }
    
    /**
     * @return Identity
     */
    public function getIdentity()
    {
        return $this->_identity;
    }
    
    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }
}
