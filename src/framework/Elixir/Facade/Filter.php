<?php

namespace Elixir\Facade;

use Elixir\Facade\FacadeAbstract;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Filter extends FacadeAbstract
{
    /**
     * @var array
     */
    protected static $_macros = [];

    /**
     * @param string $pMethod
     * @param callable $pMacro
     */
    public static function macro($pMethod, callable $pMacro)
    {
        static::$_macros[$pMethod] = $pMacro;
    }
    
    /**
     * @see FacadeAbstract::getFacadeAccessor()
     */
    protected static function getFacadeAccessor()
    {
        return null;
    }
    
    /**
     * @see FacadeAbstract::resolveInstance()
     */
    public static function resolveInstance($pKey)
    {
        $className = $pKey;
        $key = $pKey;
        
        if(substr($pKey, 0, 7) != 'filter.')
        {
            if(strpos($key, '\\'))
            {
                $key = explode('\\', $key);
                $key = end($key);
            }
            else if(strpos($key, '_'))
            {
                $key = explode('_', $key);
                $key = end($key);
            }
            
            $key = 'filter.' . strtolower($key);
        }
        
        $instance = parent::resolveInstance($key);
        
        if(null === $instance)
        {
            if(class_exists($className))
            {
                $instance = new $className();
                static::$_resolvedInstances[$className] = $instance;
            }
        }
        
        return $instance;
    }
    
    /**
     * @param string $pClassOrKey
     * @param string $pContent
     * @param array $pOptions
     * @return mixed
     */
    public static function filter($pClassOrKey, $pContent, array $pOptions = [])
    {
        $filter = static::resolveInstance($pClassOrKey);
        return $filter->filter($pContent, $pOptions);
    }
    
    /**
     * @param string $pMethod
     * @param array $pArguments
     * @return mixed
     * @throws \LogicException
     */
    public static function __callStatic($pMethod, $pArguments)
    {
        if(isset(static::$_macros[$pMethod]))
        {
            return call_user_func_array(static::$_macros[$pMethod], $pArguments);
        }
        
        throw new \LogicException('No filter instance in dependency injection container.');
    }
}
