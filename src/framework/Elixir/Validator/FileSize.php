<?php

namespace Elixir\Validator;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class FileSize extends ValidatorAbstract
{
    /**
     * @var string
     */
    const MAX_ERROR = 'error_max';
    
    /**
     * @var string
     */
    const MIN_ERROR = 'error_min';
    
    /**
     * @var array 
     */
    protected $_errorMessageTemplates = array(
        self::ERROR => 'File size is invalid.',
        self::MAX_ERROR => 'File is too large.',
        self::MIN_ERROR => 'File is too light.'
    );
    
    /**
     * @see ValidatorInterface::isValid()
     */
    public function isValid($pContent, array $pOptions = array()) 
    {
        $pOptions = array_merge($this->_options, $pOptions);
        $this->_errors = array();
        
        $size = is_array($pContent) ? $pContent['size'] : filesize($pContent);
        
        if(isset($pOptions['max']))
        {
            $max = $pOptions['max'];

            if($size > $max)
            {
                $this->_errors[] = $this->getErrorMessageTemplate(self::MAX_ERROR);
            }
        }
        
        if(isset($pOptions['min']))
        {
            $min = $pOptions['min'];

            if($size < $min)
            {
                $this->_errors[] = $this->getErrorMessageTemplate(self::MIN_ERROR);
            }
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