<?php
namespace Elixir\Validator;

use Elixir\Validator\ValidatorAbstract;
use Elixir\Validator\ValidatorInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Regex extends ValidatorAbstract
{
    /**
     * @var array 
     */
    protected $_errorMessageTemplates = [self::ERROR => 'No match found.'];
    
    /**
     * @see ValidatorInterface::isValid()
     */
    public function isValid($pContent, array $pOptions = [])
    {
        $pOptions = array_merge($this->_options, $pOptions);
        $this->_errors = [];
        
        if(is_array($pContent) && count($pContent) > 0)
        {
            $pContent = implode('', $pContent);
        }
        
        if(empty($pContent))
        {
            $pContent = '';
        }
        
        $regex = $pOptions['regex'];
        $match = isset($pOptions['match']) ? $pOptions['match'] : true;
        
        if(preg_match($regex, $pContent) == !$match)
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