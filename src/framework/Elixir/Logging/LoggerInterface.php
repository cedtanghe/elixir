<?php

namespace Elixir\Logging;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface LoggerInterface
{
    /**
     * @var integer
     */
    const EMERG = 0;
    
    /**
     * @var integer
     */
    const ALERT = 1;
    
    /**
     * @var integer
     */
    const CRIT  = 2;
    
    /**
     * @var integer
     */
    const ERR = 3;
    
    /**
     * @var integer
     */
    const WARN = 4;
    
    /**
     * @var integer
     */
    const NOTICE = 5;
    
    /**
     * @var integer
     */
    const INFO = 6;
    
    /**
     * @var integer
     */
    const DEBUG = 7;
    
    /**
     * @param string $pMessage
     * @param integer $pSeverity
     */
    public function log($pMessage, $pSeverity = self::INFO);
}