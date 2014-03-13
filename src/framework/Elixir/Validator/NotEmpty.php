<?php

namespace Elixir\Validator;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class NotEmpty extends Regex
{
    /**
     * @var array 
     */
    protected $_errorMessageTemplates = array(self::ERROR => 'String is empty.');
    
    /**
     * @see Regex::isValid()
     */
    public function isValid($pContent, array $pOptions = array()) 
    {
        return parent::isValid(
            $pContent, 
            array_merge(
                array_merge($this->_options, $pOptions), 
                array('regex' => '/^\s*$/', 'match' => false)
            )
        );
    }
}