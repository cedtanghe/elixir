<?php

namespace Elixir\Validator;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class Range extends ValidatorAbstract
{
    /**
     * @var string
     */
    const NUMERIC_ERROR = 'error_numeric';
    
    /**
     * @var string
     */
    const RANGE_ERROR = 'error_range';
    
    /**
     * @var array 
     */
    protected $_errorMessageTemplates = array(
        self::ERROR => 'Error when validating.',
        self::NUMERIC_ERROR => 'Value is not numeric.',
        self::RANGE_ERROR => 'Value is outside this range.'
    );
    
    /**
     * @see ValidatorInterface::isValid()
     */
    public function isValid($pContent, array $pOptions = array()) 
    {
        $pOptions = array_merge($this->_options, $pOptions);
        $this->_errors = array();
        
        $min = isset($pOptions['min']) ? $pOptions['min'] : null;
        $max = isset($pOptions['max']) ? $pOptions['max'] : null;
        
        if(!is_numeric($pContent))
        {
            $this->_errors[] = $this->getErrorMessageTemplate(self::NUMERIC_ERROR);
        }
        else if((null !== $min && $pContent < $min) || (null !== $max && $pContent > $max))
        {
            $this->_errors[] = $this->getErrorMessageTemplate(self::RANGE_ERROR);
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