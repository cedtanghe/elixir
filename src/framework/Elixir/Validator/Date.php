<?php

namespace Elixir\Validator;

use Elixir\Validator\ValidatorAbstract;
use Elixir\Validator\ValidatorInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Date extends ValidatorAbstract
{
    /**
     * @var string
     */
    const DEFAULT_FORMAT = 'Y-m-d';
    
    /**
     * @var array 
     */
    protected $_errorMessageTemplates = [self::ERROR => 'Value is not a valid date.'];
    
    /**
     * @see ValidatorInterface::isValid()
     */
    public function isValid($pContent, array $pOptions = []) 
    {
        $pOptions = array_merge($this->_options, $pOptions);
        $this->_errors = [];
        
        if(!$pContent instanceof \DateTime)
        {
            if(is_int($pContent))
            {
                $format = 'U';
                
                try 
                {
                    $date = new \DateTime(sprintf('@%d', $pContent)); 
                    
                    if(false === $date || $date->format($format) != $pContent)
                    {
                        $this->_errors[] = $this->getErrorMessageTemplate(self::ERROR);
                    }
                }
                catch (\Exception $e)
                {
                    $this->_errors[] = $this->getErrorMessageTemplate(self::ERROR);
                }
            }
            else
            {
                $format = isset($pOptions['format']) ? $pOptions['format'] : self::DEFAULT_FORMAT;
                $date = \DateTime::createFromFormat($format, $pContent);
                
                if(false === $date || $date->format($format) != $pContent)
                {
                    $this->_errors[] = $this->getErrorMessageTemplate(self::ERROR);
                }
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