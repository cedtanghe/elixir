<?php

namespace Elixir\Form;

use Elixir\Dispatcher\Event;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class FormEvent extends Event
{
    /**
     * @var string
     */
    const PREPARE = 'prepare';
    
    /**
     * @var string
     */
    const PRE_BIND = 'pre_bind';
    
    /**
     * @var string
     */
    const BIND = 'bind';
    
    /**
     * @var string
     */
    const PRE_SUBMIT = 'pre_submit';
    
    /**
     * @var string
     */
    const PRE_SUBMIT_VALIDATION = 'pre_submit_validation';
    
    /**
     * @var string
     */
    const SUBMIT = 'submit';
    
    /**
     * @var string
     */
    const RESET = 'reset';
    
    /**
     * @var string
     */
    const PRE_VALUES = 'pre_values';
    
    /**
     * @var string
     */
    const VALUES = 'values';
    
    /**
     * @var string
     */
    const RENAME = 'rename';
    
    /**
     * @var array
     */
    protected $_data;
    
    /**
     * @see Event::__contruct()
     * @param array $pData
     */
    public function __construct($pType, array $pData = null) 
    {
        parent::__construct($pType);
        $this->_data = $pData;
    }
    
    /**
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }
    
    /**
     * @param array $pValue
     */
    public function setData(array $pValue)
    {
        $this->_data = $pValue;
    }
}
