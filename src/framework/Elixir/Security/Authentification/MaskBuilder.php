<?php

namespace Elixir\Security\Authentification;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class MaskBuilder
{
    /**
     * @var integer 
     */
    protected $_code;
    
    /**
     * @param integer $pCode
     */
    public function __construct($pCode = 0)
    {
        $this->_code = $pCode;
    }
    
    /**
     * @param array $pReferences
     * @return array
     */
    public function explode(array $pReferences)
    {
        $codes = array();
        
        foreach($pReferences as $code)
        {
            if($this->has($code))
            {
                $codes[] = $code;
            }
        }
        
        return $codes;
    }
    
    /**
     * @param integer $pCode
     * @return boolean
     */
    public function has($pCode)
    {
        return ($this->_code & $pCode) == $pCode;
    }
    
    /**
     * @param integer $pCode
     */
    public function add($pCode)
    {
        if(!$this->has($pCode))
        {
            $this->_code |= $pCode;
        }
    }
    
    /**
     * @param integer $pCode
     */
    public function remove($pCode)
    {
        if(!$this->has($pCode))
        {
            $this->_code ^= $pCode;
        }
    }
    
    /**
     * @param integer $pValue
     */
    public function set($pValue)
    {
        $this->_code = $pValue;
    }
    
    /**
     * @return integer
     */
    public function get()
    {
        return $this->_code;
    }
    
    /**
     * @return boolean
     */
    public function isEmpty()
    {
        return $this->_code == 0;
    }
}