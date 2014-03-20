<?php

namespace Elixir;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class Version
{
    /**
     * @var string
     */
    const PHP_VERSION_MIN = '5.3.3';
    
    /**
     * @var string
     */
    const VERSION = '2.0.0 : 2014-04-20';
    
    /**
     * @return string 
     */
    public static function getCode()
    {
        $d = explode(':', self::VERSION);
        return trim($d[0]);
    }
    
    /**
     * @return \DateTime 
     */
    public static function getLastModification()
    {
        $d = explode(':', self::VERSION);
        return new \DateTime(trim($d[1]));
    }
}
