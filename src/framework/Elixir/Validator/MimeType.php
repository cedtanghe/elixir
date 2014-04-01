<?php

namespace Elixir\Validator;

use Elixir\Util\File;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class MimeType extends ValidatorAbstract
{
    /**
     * @var array 
     */
    protected $_errorMessageTemplates = array(self::ERROR => 'Mime type of file is invalid.');
    
    /**
     * @see ValidatorInterface::isValid()
     */
    public function isValid($pContent, array $pOptions = array()) 
    {
        $pOptions = array_merge($this->_options, $pOptions);
        $this->_errors = array();
        
        $found = false;
        $content = is_array($pContent) ? $pContent['tmp_name'] : $pContent;
        
        if(isset($pOptions['mimeType']))
        {
            $mimeTypes = (array)$pOptions['mimeType'];
            $fileMimeType = strtolower(File::mimeType($content));
            
            foreach((array)$mimeTypes as $mimeType)
            {
                if(strtolower($mimeType) == $fileMimeType)
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