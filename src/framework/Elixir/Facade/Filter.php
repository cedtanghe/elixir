<?php

namespace Elixir\Facade;

use Elixir\Facade\FacadeAbstract;
use Elixir\Filter\Escaper;
use Elixir\Filter\NbrFormat;

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
     * @throws LogicException
     */
    public static function __callStatic($pMethod, $pArguments)
    {
        if(isset(static::$_macros[$pMethod]))
        {
            return call_user_func_array(static::$_macros[$pMethod], $pArguments);
        }
        
        throw new LogicException('No filter instance in dependency injection container.');
    }
    
    public static function register() 
    {
        /************ SINGLE ************/
        
        static::macro(
            'single', 
            function($pContent, array $pFilters)
            {
                foreach($pFilters as $filter => $options)
                {
                    if(isset(static::$_macros[$filter]))
                    {
                        $arguments = array_unshift(array_slice($options, 0), $pContent);
                        $pContent = call_user_func_array(static::$_macros[$filter], $arguments);
                    }
                    else
                    {
                        $pContent = static::filter($filter, $pContent, $options);
                    }
                }
                
                return $pContent;
            }
        );
        
        /************ GROUP ************/
        
        static::macro(
            'group', 
            function($pProvider, array $pFilters)
            {
                foreach($pFilters as $key => $filters)
                {
                    if(isset($pProvider[$key]))
                    {
                        continue;
                    }
                    
                    foreach($filters as $filter => $options)
                    {
                        if(isset(static::$_macros[$filter]))
                        {
                            $arguments = array_unshift(array_slice($options, 0), $pContent);
                            $pProvider[$key] = call_user_func_array(static::$_macros[$filter], $arguments);
                        }
                        else
                        {
                            $pProvider[$key] = static::filter($filter, $pProvider[$key], $options);
                        }
                    }
                }
                
                return $pProvider;
            }
        );
        
        /************ BOOLEAN ************/
        
        static::macro(
            'boolean', 
            function($pContent, array $pOptions = [])
            {
                return static::filter('\Elixir\Filter\Boolean', $pContent, $pOptions);
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
            
                return static::filter('\Elixir\Filter\Callback', $pContent, $pOptions);
            }
        );
        
        /************ CHAIN ************/
        
        static::macro(
            'chain', 
            function($pContent, array $pFilters)
            {
                $chain = static::resolveInstance('\Elixir\Filter\Chain');
                $chain->setFilters($pFilters);
                
                return $chain->filter();
            }
        );
        
        /************ CROP ************/
        
        static::macro(
            'crop', 
            function($pContent, array $pOptions = [])
            {
                return static::filter('\Elixir\Filter\Crop', $pContent, $pOptions);
            }
        );
        
        /************ DATE ************/
        
        static::macro(
            'date', 
            function($pContent, array $pOptions = [])
            {
                return static::filter('\Elixir\Filter\Date', $pContent, $pOptions);
            }
        );
        
        /************ DUPLICATE ************/
        
        static::macro(
            'duplicate', 
            function($pContent, array $pOptions = [])
            {
                return static::filter('\Elixir\Filter\Duplicate', $pContent, $pOptions);
            }
        );
        
        /************ EMAIL ************/
        
        static::macro(
            'email', 
            function($pContent, array $pOptions = [])
            {
                return static::filter('\Elixir\Filter\Email', $pContent, $pOptions);
            }
        );
        
        /************ ENLARGE ************/
        
        static::macro(
            'enlarge', 
            function($pContent, array $pOptions = [])
            {
                return static::filter('\Elixir\Filter\Enlarge', $pContent, $pOptions);
            }
        );
        
        /************ ESCAPER ************/
        
        static::macro(
            'escape', 
            function($pContent, array $pOptions = [])
            {
                return static::filter('\Elixir\Filter\Escaper', $pContent, $pOptions);
            }
        );
        
        static::macro(
            'escapeHTML', 
            function($pContent, array $pOptions = [])
            {
                $pOptions = array_merge(['strategy' => Escaper::HTML], $pOptions);
                return static::filter('\Elixir\Filter\Escaper', $pContent, $pOptions);
            }
        );
        
        static::macro(
            'escapeXML', 
            function($pContent, array $pOptions = [])
            {
                $pOptions = array_merge(['strategy' => Escaper::XML], $pOptions);
                return static::filter('\Elixir\Filter\Escaper', $pContent, $pOptions);
            }
        );
        
        static::macro(
            'escapeHTMLAttr', 
            function($pContent, array $pOptions = [])
            {
                $pOptions = array_merge(['strategy' => Escaper::HTML_ATTR], $pOptions);
                return static::filter('\Elixir\Filter\Escaper', $pContent, $pOptions);
            }
        );
        
        static::macro(
            'escapeXMLAttr', 
            function($pContent, array $pOptions = [])
            {
                $pOptions = array_merge(['strategy' => Escaper::XML_ATTR], $pOptions);
                return static::filter('\Elixir\Filter\Escaper', $pContent, $pOptions);
            }
        );
        
        static::macro(
            'escapeJS', 
            function($pContent, array $pOptions = [])
            {
                $pOptions = array_merge(['strategy' => Escaper::JS], $pOptions);
                return static::filter('\Elixir\Filter\Escaper', $pContent, $pOptions);
            }
        );
        
        static::macro(
            'escapeCSS', 
            function($pContent, array $pOptions = [])
            {
                $pOptions = array_merge(['strategy' => Escaper::CSS], $pOptions);
                return static::filter('\Elixir\Filter\Escaper', $pContent, $pOptions);
            }
        );
        
        static::macro(
            'escapeURL', 
            function($pContent, array $pOptions = [])
            {
                $pOptions = array_merge(['strategy' => Escaper::URL], $pOptions);
                return static::filter('\Elixir\Filter\Escaper', $pContent, $pOptions);
            }
        );
        
        /************ FLOAT ************/
        
        static::macro(
            'float', 
            function($pContent, array $pOptions = [])
            {
                return static::filter('\Elixir\Filter\Float', $pContent, $pOptions);
            }
        );
        
        /************ INTEGER ************/
        
        static::macro(
            'int', 
            function($pContent)
            {
                return static::filter('\Elixir\Filter\Int', $pContent);
            }
        );
        
        /************ NUMBER FORMAT ************/
        
        static::macro(
            'nbrFormat', 
            function($pContent, array $pOptions = [])
            {
                return static::filter('\Elixir\Filter\NbrFormat', $pContent, $pOptions);
            }
        );
        
        static::macro(
            'formatNumber', 
            function($pContent, array $pOptions = [])
            {
                $pOptions = array_merge(['strategy' => NbrFormat::FORMAT], $pOptions);
                return static::filter('\Elixir\Filter\NbrFormat', $pContent, $pOptions);
            }
        );
        
        static::macro(
            'formatCurrency', 
            function($pContent, array $pOptions = [])
            {
                $pOptions = array_merge(['strategy' => NbrFormat::FORMAT_CURRENCY], $pOptions);
                return static::filter('\Elixir\Filter\NbrFormat', $pContent, $pOptions);
            }
        );
        
        /************ PROTECT ************/
        
        static::macro(
            'protect', 
            function($pContent)
            {
                return static::filter('\Elixir\Filter\Protect', $pContent);
            }
        );
        
        /************ RENAME ************/
        
        static::macro(
            'rename', 
            function($pContent, array $pOptions = [])
            {
                return static::filter('\Elixir\Filter\Rename', $pContent, $pOptions);
            }
        );
        
        /************ REPLACE ************/
        
        static::macro(
            'replace', 
            function($pContent, array $pOptions = [])
            {
                return static::filter('\Elixir\Filter\Replace', $pContent, $pOptions);
            }
        );
        
        /************ RESIZE ************/
        
        static::macro(
            'resize', 
            function($pContent, array $pOptions = [])
            {
                return static::filter('\Elixir\Filter\Resize', $pContent, $pOptions);
            }
        );
        
        /************ TRIM ************/
        
        static::macro(
            'trim', 
            function($pContent, array $pOptions = [])
            {
                return static::filter('\Elixir\Filter\Trim', $pContent, $pOptions);
            }
        );
    }
}
