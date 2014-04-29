<?php

namespace Elixir\Validator;

use Elixir\Validator\ValidatorAbstract;
use Elixir\Validator\ValidatorInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Length extends ValidatorAbstract
{
    /**
     * @var array 
     */
    protected $_errorMessageTemplates = array(self::ERROR => 'The size of string is invalid.');
    
    /**
     * @see ValidatorInterface::isValid()
     */
    public function isValid($pContent, array $pOptions = array()) 
    {
        $pOptions = array_merge($this->_options, $pOptions);
        $this->_errors = array();
        
        $len = isset($pOptions['mb']) && $pOptions['mb'] ? mb_strlen($pContent) : strlen($pContent);
        $min = isset($pOptions['min']) ? $pOptions['min'] : null;
        $max = isset($pOptions['max']) ? $pOptions['max'] : null;
        
        if((null !== $min && $len < $min) || (null !== $max && $len > $max))
        {
            $this->_errors[] = str_replace(array('{MIN}', '{MAX}'), 
                                           array($min, $max),
                                           $this->getErrorMessageTemplate(self::ERROR));
        }
        
        if($this->hasError())
        {
            if(isset($pOptions['error']))
            {
                $this->_errors = (array)str_replace(array('{MIN}', '{MAX}'), 
                                                    array($min, $max),
                                                    $pOptions['error']);
            }
            
            return false;
        }
        
        return true;
    }
}