<?php

namespace Elixir\Validator;

use Elixir\Validator\Regex;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class NotEmpty extends Regex
{
    /**
     * @var array 
     */
    protected $_errorMessageTemplates = [self::ERROR => 'String is empty.'];
    
    /**
     * @see Regex::isValid()
     */
    public function isValid($pContent, array $pOptions = []) 
    {
        return parent::isValid(
            $pContent, 
            array_merge(
                array_merge($this->_options, $pOptions), 
                ['regex' => '/^\s*$/', 'match' => false]
            )
        );
    }
}
