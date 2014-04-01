<?php

namespace Elixir\Validator;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

abstract class ValidatorAbstract implements ValidatorInterface
{
    /**
     * @var string
     */
    const ERROR = 'error';
    
    /**
     * @var string
     */
    protected $_errorMessage;
    
    /**
     * @var array
     */
    protected $_errors = array();
    
    /**
     * @var array 
     */
    protected $_errorMessageTemplates = array(self::ERROR => 'Error when validating');
    
    /**
     * @var array
     */
    protected $_options = array();

    /**
     * @param array $pValue
     */
    public function setDefaultOptions(array $pValue)
    {
        $this->_options = $pValue;
    }
    
    /**
     * @return array
     */
    public function getDefaultOptions()
    {
        return $this->_options;
    }

    /**
     * @param string $pValue
     */
    public function setErrorMessage($pValue)
    {
        $this->_errorMessage = $pValue;
    }
    
    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->_errorMessage;
    }
    
    /**
     * @param string $pKey
     * @param string $pValue
     */
    public function setErrorMessageTemplate($pKey, $pValue)
    {
        $this->_errorMessageTemplates[$pKey] = $pValue;
    }
    
    /**
     * @param string $pKey
     * @param mixed $pDefault
     * @return mixed
     */
    public function getErrorMessageTemplate($pKey, $pDefault = null)
    {
        if(isset($this->_errorMessageTemplates[$pKey]))
        {
            return $this->_errorMessageTemplates[$pKey];
        }
        
        if(is_callable($pDefault))
        {
            return call_user_func($pDefault);
        }
        
        return $pDefault;
    }
    
    /**
     * @return boolean
     */
    public function hasError()
    {
        return count($this->_errors) > 0;
    }
    
    /**
     * @see ValidatorInterface::errors();
     */
    public function errors()
    {
        if(!$this->hasError())
        {
            return null;
        }
        
        if(null !== $this->_errorMessage)
        {
            return $this->_errorMessage;
        }
        
        return count($this->_errors) == 1 ? $this->_errors[0] : $this->_errors;
    }
}

