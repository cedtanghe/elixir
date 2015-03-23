<?php

namespace Elixir\Logging\Writer;

use Elixir\Logging\LoggerInterface;
use Elixir\Logging\Writer\WriterInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

abstract class WriterAbstract implements WriterInterface
{
    /**
     * @var string
     */
    protected $_timeFormat = 'Y-m-d G:i:s';
    
    /**
     * @var string 
     */
    protected $_messageTemplate = "{SEPARATOR}\n{SEVERITY} - {DATE}\n{SEPARATOR}\n{MESSAGE}";
    
    /**
     * @var array
     */
    protected $_locked = [];
    
    /**
     * @param string $pValue
     */
    public function setTimeFormat($pValue)
    {
        $this->_timeFormat = $pValue;
    }
    
    /**
     * @return string
     */
    public function getTimeFormat()
    {
        return $this->_timeFormat;
    }
    
    /**
     * @return string
     */
    public function getMessageTemplate()
    {
        return $this->_messageTemplate;
    }
    
    /**
     * @param string $pValue
     */
    public function setMessageTemplate($pValue)
    {
        $this->_messageTemplate = $pValue;
    }
    
    /**
     * @param integer $pSeverity
     * @return boolean
     */
    public function isLocked($pSeverity)
    {
        return in_array($pSeverity, $this->_locked);
    }
    
    /**
     * @param integer $pSeverity
     */
    public function lock($pSeverity)
    {
        if(!$this->isLocked($pSeverity))
        {
            $this->_locked[] = $pSeverity;
        }
    }
    
    /**
     * @param integer $pSeverity
     */
    public function unlock($pSeverity)
    {
        $pos = array_search($pSeverity, $this->_locked);
        
        if(false !== $pos)
        {
            array_splice($this->_locked, $pos, 1);
        }
    }

    /**
     * @return boolean
     */
    abstract public function clear();
    
    /**
     * @param string $pSeverity
     * @return string|null
     */
    protected function formatSeverity($pSeverity)
    {
        switch($pSeverity)
        {
            case LoggerInterface::EMERG:
                return 'Emergency';
            break;
            case LoggerInterface::ALERT:
                return 'Alert';
            break;
            case LoggerInterface::CRIT:
                return 'Critical';
            break;
            case LoggerInterface::ERR:
                return 'Error';
            break;
            case LoggerInterface::WARN:
                return 'Warning';
            break;
            case LoggerInterface::NOTICE:
                return 'Notice';
            break;
            case LoggerInterface::INFO:
                return 'Informational';
            break;
            case LoggerInterface::DEBUG:
                return 'Debug';
            break;
        }
        
        return null;
    }

    /**
     * @param string $pMessage
     * @param integer $pSeverity
     * @return string
     */
    protected function format($pMessage, $pSeverity)
    {
        $template = $this->_messageTemplate;
        $message = str_replace('{MESSAGE}', $pMessage, $template);
        $message = str_replace('{SEVERITY}', $this->formatSeverity($pSeverity), $message);
        $message = str_replace('{SEPARATOR}', str_repeat('-', 100), $message);
        $message = str_replace('{DATE}', date($this->_timeFormat), $message);
        
        return $message;
    }
}
