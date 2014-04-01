<?php

namespace Elixir\Validator;

use Elixir\Form\Field\FieldInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Equal extends ValidatorAbstract
{
    /**
     * @var array 
     */
    protected $_errorMessageTemplates = array(self::ERROR => 'Value is not equal to the comparison value.');
    
    /**
     * @see ValidatorInterface::isValid()
     */
    public function isValid($pContent, array $pOptions = array()) 
    {
        $pOptions = array_merge($this->_options, $pOptions);
        $this->_errors = array();
        
        $compare = $pOptions['compare'];
        $compareName = '';
        
        if($compare instanceof FieldInterface)
        {
            $compareName = $compare->getName();
            $compare = $compare->getValue(false);
        }
        
        $strict = isset($pOptions['strict']) ? $pOptions['strict'] : false; 
        
        if($strict && $compare !== $pContent)
        {
            $this->_errors[] = str_replace('{NAME}', $compareName, $this->getErrorMessageTemplate(self::ERROR));
        }
        else if($compare != $pContent)
        {
             $this->_errors[] = str_replace('{NAME}', $compareName, $this->getErrorMessageTemplate(self::ERROR));
        }
        
        if($this->hasError())
        {
            if(isset($pOptions['error']))
            {
                $this->_errors = (array)str_replace('{NAME}', $compareName, $pOptions['error']);
            }
            
            return false;
        }
        
        return true;
    }
}