<?php

namespace Elixir\Facade;

use Elixir\Facade\FacadeAbstract;
use Elixir\View\ViewInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class View extends FacadeAbstract
{
    /**
     * @var ViewInterface; 
     */
    protected static $_cloned = null;

    /**
     * @see FacadeAbstract::getFacadeAccessor()
     */
    protected static function getFacadeAccessor()
    {
        return 'view';
    }
    
    /**
     * @param string $pTemplate
     * @param array $pData
     * @return string
     */
    public static function make($pTemplate, array $pData = [])
    {
        if(null === static::$_cloned)
        {
            $instance = static::resolveInstance(static::getFacadeAccessor());
            static::$_cloned = clone $instance;
        }
        
        return static::$_cloned->render($pTemplate, $pData);
    }
}
