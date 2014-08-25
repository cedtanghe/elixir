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
        
        if(substr($pKey, 0, 10) != 'validator.')
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
     * @return boolean|array
     */
    public static function valid($pClassOrKey, $pContent, array $pOptions = [])
    {
        $validator = static::resolveInstance($pClassOrKey);
        $valid = $validator->isValid($pContent, $pOptions);
        
        if(isset($pOptions['with-errors']) && $pOptions['with-errors'])
        {
            return ['valid' => $valid, 'errors' => $validator->errors()];
        }
        else
        {
            return $valid;
        }
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
