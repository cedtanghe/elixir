<?php

namespace Elixir\Validator;

use Elixir\Util\Image;
use Elixir\Validator\ValidatorAbstract;
use Elixir\Validator\ValidatorInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Format extends ValidatorAbstract
{
    /**
     * @var string
     */
    const MAX_WIDTH_ERROR = 'error_max_width';
    
    /**
     * @var string
     */
    const MIN_WIDTH_ERROR = 'error_min_width';
    
    /**
     * @var string
     */
    const MAX_HEIGHT_ERROR = 'error_max_height';
    
    /**
     * @var string
     */
    const MIN_HEIGHT_ERROR = 'error_min_height';
    
    /**
     * @var array 
     */
    protected $_errorMessageTemplates = [
        self::ERROR => 'Dimensions of the file are invalid.',
        self::MAX_WIDTH_ERROR => 'Maximum width of file is invalid.',
        self::MAX_HEIGHT_ERROR => 'Maximum height of file is invalid.',
        self::MIN_WIDTH_ERROR => 'Minimum width of file is invalid.',
        self::MIN_HEIGHT_ERROR => 'Minimum height of file is invalid.'
    ];
    
    /**
     * @see ValidatorInterface::isValid()
     */
    public function isValid($pContent, array $pOptions = []) 
    {
        $pOptions = array_merge($this->_options, $pOptions);
        $this->_errors = [];
        
        $infos = Image::getSizingInfo(is_array($pContent) ? $pContent['tmp_name'] : $pContent);
        $width = $infos['srcWidth'];
        $height = $infos['srcHeight'];
        
        $wMin = isset($pOptions['wMin']) ? $pOptions['wMin'] : 0;
        $hMin = isset($pOptions['hMin']) ? $pOptions['hMin'] : 0;
        $wMax = isset($pOptions['wMax']) ? $pOptions['wMax'] : -1;
        $hMax = isset($pOptions['hMax']) ? $pOptions['hMax'] : -1;
        
        if($wMax != -1 && $wMin > $wMax)
        {
            $wMax = $wMin;
        }
        
        if($hMax != -1 && $hMin > $hMax)
        {
            $hMax = $wMin;
        }
        
        if($width < $wMin)
        {
            $this->_errors[] = $this->getErrorMessageTemplate(self::MIN_WIDTH_ERROR);
        }
        else if(-1 != $wMax && $width > $wMax)
        {
            $this->_errors[] = $this->getErrorMessageTemplate(self::MAX_WIDTH_ERROR);
        }
        
        if($height < $hMin)
        {
            $this->_errors[] = $this->getErrorMessageTemplate(self::MIN_HEIGHT_ERROR);
        }
        else if(-1 != $hMax && $height > $hMax)
        {
            $this->_errors[] = $this->getErrorMessageTemplate(self::MAX_HEIGHT_ERROR);
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
