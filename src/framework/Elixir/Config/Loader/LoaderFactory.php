<?php

namespace Elixir\Config\Loader;

use Elixir\Config\Loader\Arr;
use Elixir\Config\Loader\JSON;
use Elixir\Config\Loader\LoaderInterface;
use Elixir\Config\Loader\YAML;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class LoaderFactory 
{
    /**
     * @var array 
     */
    public static $loaders = [];

    /**
     * @param mixed $config
     * @param array $options
     * @return LoaderInterface
     * @throws \InvalidArgumentException
     */
    public static function create($config, array $options = []) 
    {
        if(!isset(static::$loaders['Arr']))
        {
            static::$loaders['Arr'] = function($config, $options)
            {
                if (is_array($config) || strstr($config, '.php'))
                {
                    return new Arr($options['environment'], $options['strict']);
                }
                
                return null;
            };
        }
        
        if(!isset(static::$loaders['JSON']))
        {
            static::$loaders['JSON'] = function($config, $options)
            {
                if (substr($config, -5) == '.json')
                {
                    return new JSON($options['environment'], $options['strict']);
                }
                
                return null;
            };
        }
        
        if(!isset(static::$loaders['YAML']))
        {
            static::$loaders['YAML'] = function($config, $options)
            {
                if (substr($config, -4) == '.yml')
                {
                    return new YAML($options['environment'], $options['strict']);
                }
                
                return null;
            };
        }
        
        $options['environment'] = isset($options['environment']) ? $options['environment'] : null;
        $options['strict'] = isset($options['strict']) ? $options['strict'] : false;
        
        foreach(static::$loaders as $loader)
        {
            $result = $loader($config, $options);
            
            if(null !== $result)
            {
                return $result;
            }
        }
        
        throw new \InvalidArgumentException('No loader has been implemented for this type of resource.');
    }
}
