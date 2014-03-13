<?php

namespace Elixir\Dispatcher;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class Event
{
    /**
     * @var string
     */
    const START = 'start';
    
    /**
     * @var string
     */
    const CHANGE = 'change';
    
    /**
     * @var string
     */
    const COMPLETE = 'complete';
    
    /**
     * @var string
     */
    const CLOSE = 'close';
    
    /**
     * @var string
     */
    const ERROR = 'error';
    
    /**
     * @var string
     */
    const UPDATE = 'update';
    
    /**
     * @var string
     */
    const CANCEL = 'cancel';
    
    /**
     * @var string
     */
    const DESTROY = 'destroy';

    /**
     * @var string
     */
    protected $_type;
    
    /**
     * @var mixed
     */
    protected $_target;
    
    /**
     * @var boolean
     */
    protected $_stopPropagation = false;
    
    /**
     * @param string $pType 
     */
    public function __construct($pType) 
    {
        $this->_type = $pType;
    }
    
    /**
     * @return string 
     */
    public function getType()
    {
        return $this->_type;
    }
    
    /**
     * @return mixed
     */
    public function getTarget()
    {
        return $this->_target;
    }
    
    /**
     * @param mixed $pValue 
     */
    public function setTarget($pValue)
    {
        $this->_target = $pValue;
    }
    
    /**
     * @return boolean 
     */
    public function isStopped()
    {
        return $this->_stopPropagation;
    }
    
    public function stopPropagation()
    {
        $this->_stopPropagation = true;
    }
}
