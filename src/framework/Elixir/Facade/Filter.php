<?php

namespace Elixir\Facade;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class Filter extends FacadeAbstract
{
    /**
     * @var array
     */
    protected static $_macros = array();

    /**
     * @param string $pMethod
     * @param \Closure $pMacro
     */
    public static function macro($pMethod, \Closure $pMacro)
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
                $key = end(explode('\\', $key));
            }
            else if(strpos($key, '_'))
            {
                $key = end(explode('_', $key));
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
    public static function filter($pClassOrKey, $pContent, array $pOptions = array())
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
        
        throw new \LogicException('No validator instance in dependency injection container.');
    }
}