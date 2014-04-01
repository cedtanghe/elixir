<?php

namespace Elixir\Form\Field;

use Elixir\Dispatcher\Event;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class FieldEvent extends Event
{
    /**
     * @var string
     */
    const PRE_VALIDATION = 'pre_validation';
    
    /**
     * @var mixed
     */
    protected $_value;
    
    /**
     * @see Event::__contruct()
     * @param mixed $pData
     */
    public function __construct($pType, $pValue = null) 
    {
        parent::__construct($pType);
        $this->_value = $pValue;
    }
    
    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->_value;
    }
    
    /**
     * @param mixed $pValue
     */
    public function setValue($pValue)
    {
        $this->_value = $pValue;
    }
}