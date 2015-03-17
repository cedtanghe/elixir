<?php

namespace Elixir\Logging\Writer;

use Elixir\Logging\LoggerInterface;
use Elixir\Logging\Writer\WriterAbstract;

/**
 * @see http://www.firephp.org/Wiki/Reference/Protocol
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class FirePHP extends WriterAbstract
{
    /**
     * @var string 
     */
    protected $_messageTemplate = '{MESSAGE}';
    
    /**
     * @var integer 
     */
    protected $_counter = 0;
    
    public function __construct()
    {
        if(!headers_sent())
        {
            header('X-Wf-Protocol-1: http://meta.wildfirehq.org/Protocol/JsonStream/0.2');
            header('X-Wf-1-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3');
            header('X-Wf-1-Structure-1: http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1');
        }
    }
    
    /**
     * @see WriterAbstract::clear()
     */
    public function clear()
    {
        // Not yet
    }

    /**
     * @see WriterAbstract::write()
     */
    public function write($pMessage, $pSeverity)
    {
        if(!$this->isLocked($pSeverity))
        {
            if(!headers_sent())
            {      
                switch($pSeverity)
                {
                    case LoggerInterface::EMERG:
                    case LoggerInterface::ALERT:
                    case LoggerInterface::CRIT:
                    case LoggerInterface::ERR:
                        $type = 'ERROR';
                    break;
                    case LoggerInterface::WARN:
                        $type = 'WARN';
                    break;
                    case LoggerInterface::NOTICE:
                    case LoggerInterface::INFO:
                        $type = 'INFO';
                    break;
                    case LoggerInterface::DEBUG:
                        $type = 'LOG';
                    break;
                }
                
                $content = json_encode([['Type' => $type], $this->format($pMessage, $pSeverity)]);
                header('X-Wf-1-1-1-' . ++$this->_counter . ': ' . strlen($content) . '|' . $content . '|');
            }
        }
    }
}
