<?php

namespace Elixir\Form\Field;

use Elixir\Form\Field\FieldAbstract;

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
    
    /**
     * @param string $pValue
     */
    public function setType($pValue)
    {
        $this->setAttribute('type', $pValue);
    }
    
    /**
     * @return string
     */
    public function getType()
    {
        return $this->getAttribute('type');
    }
}