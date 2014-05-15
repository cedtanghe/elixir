<?php

namespace Elixir\Facade;

use Elixir\DI\ContainerInterface;
use Elixir\MVC\Application;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

abstract class FacadeAbstract
{
    /**
     * @var ContainerInterface
     */
    protected static $_container;
    
    /**
     * @var array
     */
    protected static $_resolvedInstances = [];
    
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
     * @return string
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        throw new \RuntimeException('Facade does not implement "getFacadeAccessor" method.');
    }
    
    /**
     * @param string $pKey;
     * @return mixed
     */
    public static function resolveInstance($pKey)
    {
        if(isset(static::$_resolvedInstances[$pKey]))
        {
            return static::$_resolvedInstances[$pKey];
        }
        
        if(null === static::$_container)
        {
            if(class_exists('\Elixir\MVC\Application') && null !== Application::$registry)
            {
                static::setContainer(Application::$registry);
            }
        }
        
        if(static::$_container->has($pKey))
        {
            $instance = static::$_container->get($pKey);
            
            if(static::$_container->getStorageType($pKey) == ContainerInterface::SINGLETON)
            {
                static::$_resolvedInstances[$pKey] = $instance;
            }
            
            return $instance;
        }
        
        return null;
    }
    
    /**
     * @param string $pMethod
     * @param array $pArguments
     * @return mixed|void
     */
    public static function __callStatic($pMethod, $pArguments)
    {
        $instance = static::resolveInstance(static::getFacadeAccessor());
        return call_user_func_array([$instance, $pMethod], $pArguments);
    }
}