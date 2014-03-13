<?php

namespace Elixir\Facade;

use Elixir\DI\ContainerInterface;
use Elixir\MVC\Application;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class DI
{
    /**
     * @var ContainerInterface
     */
    protected static $_container;
    
    /**
     * @return ContainerInterface
     */
    public static function getContainer()
    {
        return static::$_container;
    }
    
    /**
     * @return ContainerInterface
     */
    public static function setContainer(ContainerInterface $pValue)
    {
        static::$_container = $pValue;
    }
    
    /**
     * @param string $pMethod
     * @param array $pArguments
     * @return mixed|void
     */
    public static function __callStatic($pMethod, $pArguments)
    {
        if(null === static::$_container)
        {
            if(class_exists('\Elixir\MVC\Application') && null !== Application::$registry)
            {
                static::setContainer(Application::$registry);
            }
        }
        
        return call_user_func_array(array(static::$_container, $pMethod), $pArguments);
    }
}