<?php

namespace Elixir\Facade;

use Elixir\Facade\FacadeAbstract;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Validator extends FacadeAbstract
{
    /**
     * @var array
     */
    protected static $_macros = [];

    /**
     * @param string $pMethod
     * @param callable $pMacro
     * @throws \InvalidArgumentException
     */
    public static function macro($pMethod, $pMacro)
    {
        if(!is_callable($pMacro))
        {
            throw new \InvalidArgumentException('Macro argument must be a callable.');
        }
        
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
        
        if(substr($pKey, 0, 10) != 'validator.')
        {
            if(strpos($key, '\\'))
            {
                $key = end(explode('\\', $key));
            }
            else if(strpos($key, '_'))
            {
                $key = end(explode('_', $key));
            }
            
            $key = 'validator.' . strtolower($key);
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
     * @return boolean
     */
    public static function valid($pClassOrKey, $pContent, array $pOptions = [])
    {
        $validator = static::resolveInstance($pClassOrKey);
        return $validator->isValid($pContent, $pOptions);
    }
    
    /**
     * @param string $pMethod
     * @param array $pArguments
     * @return boolean
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