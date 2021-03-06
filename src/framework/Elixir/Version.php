<?php

namespace Elixir;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Version
{
    /**
     * @var string
     */
    const PHP_VERSION_MIN = '5.4';
    
    /**
     * @var string
     */
    const VERSION = '2.2.0 : 2016-01-05';
    
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
