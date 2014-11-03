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
    
    public static function register() 
    {
        /************ SINGLE ************/
        
        static::macro(
            'single', 
            function($pContent, array $pValidators, array $pOptions = [])
            {
                foreach($pValidators as $validator => $options)
                {
                    if(isset(static::$_macros[$validator]))
                    {
                        $arguments = array_unshift(array_slice($options, 0), $pContent);
                        
                        if(!call_user_func_array(static::$_macros[$validator], $arguments))
                        {
                            return false;
                        }
                    }
                    else
                    {
                        $options['with-errors'] = isset($pOptions['with-errors']) ? $pOptions['with-errors'] : false;
                        $result = static::valid($validator, $pContent, $options);
                        
                        if($options['with-errors'] && !$result['valid'])
                        {
                            return $result;
                        }
                        else if(!$result)
                        {
                            return false;
                        }
                    }
                }
                
                return true;
            }
        );
        
        /************ GROUP ************/
        
        static::macro(
            'group', 
            function($pProvider, array $pValidators, array $pOptions = [])
            {
                foreach($pValidators as $key => $validators)
                {
                    if(isset($pProvider[$key]))
                    {
                        return false;
                    }
                    
                    foreach($validators as $validator => $options)
                    {
                        if(isset(static::$_macros[$validator]))
                        {
                            $arguments = array_unshift(array_slice($options, 0), $pProvider[$key]);
                            
                            if(!call_user_func_array(static::$_macros[$validator], $arguments))
                            {
                                return false;
                            }
                        }
                        else
                        {
                            $options['with-errors'] = isset($pOptions['with-errors']) ? $pOptions['with-errors'] : false;
                            $result = static::valid($validator, $pProvider[$key], $options);
                            
                            if($options['with-errors'] && !$result['valid'])
                            {
                                return $result;
                            }
                            else if(!$result)
                            {
                                return false;
                            }
                        }
                    }
                }
                
                return true;
            }
        );
        
        /************ BOOLEAN ************/
        
        static::macro(
            'boolean', 
            function($pContent, array $pOptions = [])
            {
                return static::valid('\Elixir\Validator\Boolean', $pContent, $pOptions);
            }
        );
        
        /************ CSRF ************/
        
        static::macro(
            'csrf', 
            function($pContent, array $pOptions = [])
            {
                return static::valid('validator.csrf', $pContent, $pOptions);
            }
        );
        
        /************ CALLBACK ************/
        
        static::macro(
            'callback', 
            function($pContent, $pOptions = [])
            {
                if(is_callable($pOptions))
                {
                    $pOptions = ['options' => $pOptions];
                }
            
                return static::valid('\Elixir\Validator\Callback', $pContent, $pOptions);
            }
        );
        
        /************ CHAIN ************/
        
        static::macro(
            'chain', 
            function($pContent, array $pValidators, array $pOptions = [])
            {
                $chain = static::resolveInstance('\Elixir\Validator\Chain');
                $chain->setValidators($pValidators);
                
                return $chain->isValid($pContent, $pOptions);
            }
        );
        
        /************ DATE ************/
        
        static::macro(
            'date', 
            function($pContent, array $pOptions = [])
            {
                return static::valid('\Elixir\Validator\Date', $pContent, $pOptions);
            }
        );
        
        /************ EMAIL ************/
        
        static::macro(
            'email', 
            function($pContent, array $pOptions = [])
            {
                return static::valid('\Elixir\Validator\Email', $pContent, $pOptions);
            }
        );
        
        /************ EQUAL ************/
        
        static::macro(
            'equal', 
            function($pContent, array $pOptions = [])
            {
                return static::valid('\Elixir\Validator\Equal', $pContent, $pOptions);
            }
        );
        
        /************ EXTENSION ************/
        
        static::macro(
            'extension', 
            function($pContent, array $pOptions = [])
            {
                return static::valid('\Elixir\Validator\Extension', $pContent, $pOptions);
            }
        );
        
        /************ FILE SIZE ************/
        
        static::macro(
            'fileSize', 
            function($pContent, array $pOptions = [])
            {
                return static::valid('\Elixir\Validator\FileSize', $pContent, $pOptions);
            }
        );
        
        /************ FLOAT ************/
        
        static::macro(
            'float', 
            function($pContent, array $pOptions = [])
            {
                return static::valid('\Elixir\Validator\Float', $pContent, $pOptions);
            }
        );
        
        /************ FORMAT ************/
        
        static::macro(
            'format', 
            function($pContent, array $pOptions = [])
            {
                return static::valid('\Elixir\Validator\Format', $pContent, $pOptions);
            }
        );
        
        /************ IP ************/
        
        static::macro(
            'ip', 
            function($pContent, array $pOptions = [])
            {
                return static::valid('\Elixir\Validator\IP', $pContent, $pOptions);
            }
        );
        
        /************ INTEGER ************/
        
        static::macro(
            'int', 
            function($pContent, array $pOptions = [])
            {
                return static::valid('\Elixir\Validator\Int', $pContent, $pOptions);
            }
        );
        
        /************ LENGTH ************/
        
        static::macro(
            'length', 
            function($pContent, array $pOptions = [])
            {
                return static::valid('\Elixir\Validator\Length', $pContent, $pOptions);
            }
        );
        
        /************ MIME TYPE ************/
        
        static::macro(
            'mimeType', 
            function($pContent, array $pOptions = [])
            {
                return static::valid('\Elixir\Validator\MimeType', $pContent, $pOptions);
            }
        );
        
        /************ NOT EMPTY ************/
        
        static::macro(
            'notEmpty', 
            function($pContent, array $pOptions = [])
            {
                return static::valid('\Elixir\Validator\NotEmpty', $pContent, $pOptions);
            }
        );
        
        /************ RANGE ************/
        
        static::macro(
            'range', 
            function($pContent, array $pOptions = [])
            {
                return static::valid('\Elixir\Validator\Range', $pContent, $pOptions);
            }
        );
        
        /************ REGEX ************/
        
        static::macro(
            'regex', 
            function($pContent, array $pOptions = [])
            {
                return static::valid('\Elixir\Validator\Regex', $pContent, $pOptions);
            }
        );
        
        /************ URL ************/
        
        static::macro(
            'url', 
            function($pContent, array $pOptions = [])
            {
                return static::valid('\Elixir\Validator\URL', $pContent, $pOptions);
            }
        );
    }
}
