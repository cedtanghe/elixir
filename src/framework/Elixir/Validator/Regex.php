<?php
namespace Elixir\Validator;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class Regex extends ValidatorAbstract
{
    /**
     * @var array 
     */
    protected $_errorMessageTemplates = array(self::ERROR => 'No match found.');
    
    /**
     * @see ValidatorInterface::isValid()
     */
    public function isValid($pContent, array $pOptions = array())
    {
        $pOptions = array_merge($this->_options, $pOptions);
        $this->_errors = array();
        
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