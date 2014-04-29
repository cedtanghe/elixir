<?php

namespace Elixir\Validator;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Callback extends ValidatorAbstract
{
    /**
     * @see ValidatorInterface::isValid()
     */
    public function isValid($pContent, array $pOptions = array()) 
    {
        $pOptions = array_merge($this->_options, $pOptions);
        $this->_errors = array();
        
        if(isset($pOptions['options']) || isset($pOptions['callback']))
        {
            $callable = isset($pOptions['options']) ? $pOptions['options'] : $pOptions['callback'];
            unset($pOptions['options']);
            unset($pOptions['callback']);
        }
        else
        {
            $callable = $pOptions[0];
            array_shift($pOptions);
        }
        
        if(false === $callable($pContent, $pOptions))
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