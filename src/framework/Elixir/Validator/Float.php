<?php

namespace Elixir\Validator;

use Elixir\Validator\ValidatorAbstract;
use Elixir\Validator\ValidatorInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Float extends ValidatorAbstract
{
    /**
     * @var array 
     */
    protected $_errorMessageTemplates = array(self::ERROR => 'Value is not a valid float.');
    
    /**
     * @see ValidatorInterface::isValid()
     */
    public function isValid($pContent, array $pOptions = array()) 
    {
        $pOptions = array_merge($this->_options, $pOptions);
        $this->_errors = array();
        
        if(false === filter_var($pContent, FILTER_VALIDATE_FLOAT))
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