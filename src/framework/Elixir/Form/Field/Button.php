<?php

namespace Elixir\Form\Field;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Button extends FieldAbstract
{
    /**
     * @var string
     */
    const RESET = 'reset';
    
    /**
     * @var string
     */
    const SUBMIT = 'submit';
    
    /**
     * @var string
     */
    const BUTTON = 'button';
    
    /**
     * @see FieldAbstract::__construct()
     */
    public function __construct($pName = null)
    {
        parent::__construct($pName);
        $this->_helper = 'button';
    }
}