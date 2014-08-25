<?php

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class PHPChecker_Requirement
{
    /**
     * @var string
     */
    const SUCCESS = 'success';
    
    /**
     * @var string
     */
    const FAIL = 'fail';
    
    /**
     * @var callable|boolean
     */
    protected $_assertion;
    
    /**
     * @var string
     */
    protected $_assertMessage;
    
    /**
     * @var string
     */
    protected $_successMessage;
    
    /**
     * @var string
     */
    protected $_failMessage;
    
    /**
     * @var string
     */
    protected $_helpMessage;
    
    /**
     * @var boolean
     */
    protected $_optional = false;
    
    /**
     * @var string
     */
    protected $_status;

    /**
     * @param callable|boolean $pAssertion
     * @param string $pAssertMessage
     * @param string $pSuccessMessage
     * @param string $pFailMessage
     * @param string $pHelpMessage
     * @param boolean $pOptional
     */
    public function __construct($pAssertion, 
                                $pAssertMessage, 
                                $pSuccessMessage = null, 
                                $pFailMessage = null, 
                                $pHelpMessage = null, 
                                $pOptional = false) 
    {
        $this->_assertion = $pAssertion;
        $this->_assertMessage = $pAssertMessage;
        $this->_successMessage = $pSuccessMessage;
        $this->_failMessage = $pFailMessage;
        $this->_helpMessage = $pHelpMessage;
        $this->_optional = $pOptional;
    }
    
    /**
     * @return string
     */
    public function getStatus()
    {
        if(null === $this->_status)
        {
            $this->assert();
        }
        
        return $this->_status;
    }
    
    /**
     * @return boolean
     */
    public function isSuccess()
    {
        return $this->getStatus() == self::SUCCESS;
    }
    
    /**
     * @return boolean
     */
    public function isFail()
    {
        return $this->getStatus() == self::FAIL;
    }

    /**
     * @return boolean
     */
    public function assert()
    {
        if(is_callable($this->_assertion))
        {
            $result = (bool)call_user_func_array($this->_assertion);
        }
        else
        {
            $result = (bool)$this->_assertion;
        }
        
        $this->_status = $result ? self::SUCCESS : self::FAIL;
        return $result;
    }
    
    /**
     * @return string
     */
    public function getAssertMessage()
    {
        return $this->_assertMessage;
    }
    
    /**
     * @return string
     */
    public function getSuccessMessage()
    {
        return $this->_successMessage;
    }
    
    /**
     * @return string
     */
    public function getFailMessage()
    {
        return $this->_failMessage;
    }
    
    /**
     * @return string
     */
    public function getHelpMessage()
    {
        return $this->_helpMessage;
    }
    
    /**
     * @return boolean
     */
    public function isOptional()
    {
        return $this->_optional;
    }
}
