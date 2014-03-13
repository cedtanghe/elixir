<?php

namespace Elixir\Logging;

use Elixir\Dispatcher\Dispatcher;
use Elixir\Logging\Writer\WriterInterface;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class Logger extends Dispatcher implements LoggerInterface
{
    /**
     * @var array
     */
    protected $_writers = array();
    
    /**
     * @var boolean 
     */
    protected $_disabled = false;
    
    /**
     * @param boolean $pValue
     */
    public function setDisabled($pValue)
    {
        $this->_disabled = $pValue;
    }
    
    /**
     * @return boolean
     */
    public function isDisabled()
    {
        return $this->_disabled;
    }

    /**
     * @param WriterInterface $pWriter
     */
    public function addWriter(WriterInterface $pWriter)
    {
        $this->_writers[] = $pWriter;
    }
    
    /**
     * @return array
     */
    public function getWriters()
    {
        return $this->_writers;
    }
    
    /**
     * @param array $pData
     */
    public function setWriters(array $pData)
    {
        $this->_writers = array();
        
        foreach($pData as $writer)
        {
            $this->addWriter($writer);
        }
    }

    /**
     * @param string $pMessage
     */
    public function emerg($pMessage)
    {
        $this->log($pMessage, self::EMERG);
    }
    
    /**
     * @param string $pMessage
     */
    public function alert($pMessage)
    {
        $this->log($pMessage, self::ALERT);
    }
    
    /**
     * @param string $pMessage
     */
    public function crit($pMessage)
    {
        $this->log($pMessage, self::CRIT);
    }
    
    /**
     * @param string $pMessage
     */
    public function error($pMessage)
    {
        $this->log($pMessage, self::ERR);
    }
    
    /**
     * @param string $pMessage
     */
    public function warn($pMessage)
    {
        $this->log($pMessage, self::WARN);
    }
    
    /**
     * @param string $pMessage
     */
    public function notice($pMessage)
    {
        $this->log($pMessage, self::NOTICE);
    }
    
    /**
     * @param string $pMessage
     */
    public function info($pMessage)
    {
        $this->log($pMessage, self::INFO);
    }
    
    /**
     * @param string $pMessage
     */
    public function debug($pMessage)
    {
        $this->log($pMessage, self::DEBUG);
    }
    
    /**
     * @see LoggerInterface::log()
     */
    public function log($pMessage, $pSeverity = self::INFO)
    {
        if(!$this->_disabled)
        {
            foreach($this->_writers as $writer)
            {
                $writer->write($pMessage, $pSeverity);
            }
            
            switch($pSeverity)
            {
                case self::EMERG:
                    $type = LoggerEvent::EMERG;
                break;
                case self::ALERT:
                    $type = LoggerEvent::ALERT;
                break;
                case self::CRIT:
                    $type = LoggerEvent::CRIT;
                break;
                case self::ERR:
                    $type = LoggerEvent::ERR;
                break;
                case self::WARN:
                    $type = LoggerEvent::WARN;
                break;
                case self::NOTICE:
                    $type = LoggerEvent::NOTICE;
                break;
                case self::INFO:
                    $type = LoggerEvent::INFO;
                break;
                case self::DEBUG:
                default:
                    $type = LoggerEvent::DEBUG;
                break;
            }
            
            $this->dispatch(new LoggerEvent(LoggerEvent::LOG, $pMessage, $pSeverity));
            $this->dispatch(new LoggerEvent($type, $pMessage, $pSeverity));
        }
    }
    
    /**
     * @param string $pMethod
     * @param array $pArguments
     */
    public function __call($pMethod, $pArguments) 
    {
        foreach($this->_writers as $writer)
        {
            call_user_func_array(array($writer, $pMethod), $pArguments);
        }
    }
}
