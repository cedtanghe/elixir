<?php

namespace Elixir\Logging;

use Elixir\Dispatcher\Event;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class LoggerEvent extends Event
{
    /**
     * @var string
     */
    const LOG = 'log';
    
    /**
     * @var string
     */
    const EMERG = 'emerg';
    
    /**
     * @var string
     */
    const ALERT = 'alert';
    
    /**
     * @var string
     */
    const CRIT  = 'crit';
    
    /**
     * @var string
     */
    const ERR = 'err';
    
    /**
     * @var string
     */
    const WARN = 'warn';
    
    /**
     * @var string
     */
    const NOTICE = 'notice';
    
    /**
     * @var string
     */
    const INFO = 'info';
    
    /**
     * @var string
     */
    const DEBUG = 'debug';
    
    /**
     * @var string
     */
    protected $_message;
    
    /**
     * @var integer
     */
    protected $_severity;
    
    /**
     * @see Event::__contruct()
     * @param string $pMessage
     * @param integer $pSeverity
     */
    public function __construct($pType, $pMessage = null, $pSeverity = -1) 
    {
        parent::__construct($pType);
        
        $this->_message = $pMessage;
        $this->_severity = $pSeverity;
    }
    
    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }
    
    /**
     * @return integer
     */
    public function getSeverity()
    {
        return $this->_severity;
    }
}