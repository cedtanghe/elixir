<?php

namespace Elixir\Validator;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Int extends ValidatorAbstract
{ 
    /**
     * @var array 
     */
    protected $_errorMessageTemplates = array(self::ERROR => 'Value is not a valid integer.');
    
    /**
     * @see ValidatorInterface::isValid()
     */
    public function isValid($pContent, array $pOptions = array()) 
    {
        $pOptions = array_merge($this->_options, $pOptions);
        $this->_errors = array();
        
        if(false === filter_var($pContent, FILTER_VALIDATE_INT))
        {
            $this->_errors[] = $this->getErrorMessageTemplate(self::ERROR);
        }
        
        if($this->hasError())
        {
            if(isset($pOptions['error']))
            {
                $this->_errors = (array)$pOptions['error'];
            }
            
            return false;
        }
        
        return true;
    }
}