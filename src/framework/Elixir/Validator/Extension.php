<?php

namespace Elixir\Validator;

use Elixir\Util\File;
use Elixir\Validator\ValidatorAbstract;
use Elixir\Validator\ValidatorInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Extension extends ValidatorAbstract
{
    /**
     * @var array 
     */
    protected $_errorMessageTemplates = [self::ERROR => 'Value is not a valid extension.'];
    
    /**
     * @see ValidatorInterface::isValid()
     */
    public function isValid($pContent, array $pOptions = []) 
    {
        $pOptions = array_merge($this->_options, $pOptions);
        $this->_errors = [];
        
        $found = false;
        $content = is_array($pContent) ? $pContent['name'] : $pContent;
        
        if(isset($pOptions['extension']))
        {
            $extensions = (array)$pOptions['extension'];
            $fileExtension = strtolower(File::extension($content));

            foreach($extensions as $extension)
            {
                if(strtolower($extension) == $fileExtension)
                {
                    $found = true;
                    break;
                }
            }

            if(!$found)
            {
                $this->_errors[] = $this->getErrorMessageTemplate(self::ERROR);
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