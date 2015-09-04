<?php

namespace Elixir\Config\Writer;

use Elixir\Config\Writer\Arr;
use Elixir\Config\Writer\JSON;
use Elixir\Config\Writer\WriterInterface;
use Elixir\Config\Writer\YAML;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class WriterFactory 
{
    /**
     * @var array 
     */
    public static $factories = [];

    /**
     * @param string $file
     * @return WriterInterface
     * @throws \InvalidArgumentException
     */
    public static function create($file) 
    {
        if (!isset(static::$factories['Arr']))
        {
            static::$factories['Arr'] = function($file)
            {
                if (strstr($file, '.php'))
                {
                    return new Arr();
                }
                
                return null;
            };
        }
        
        if (!isset(static::$factories['JSON']))
        {
            static::$factories['JSON'] = function($file)
            {
                if (strstr($file, '.json'))
                {
                    return new JSON();
                }
                
                return null;
            };
        }
        
        if (!isset(static::$factories['YAML']))
        {
            static::$factories['YAML'] = function($file)
            {
                if (strstr($file, '.yml'))
                {
                    return new YAML();
                }
                
                return null;
            };
        }
        
        foreach(static::$factories as $writer)
        {
            $result = $writer($file);
            
            if (null !== $result)
            {
                return $result;
            }
        }
        
        throw new \InvalidArgumentException('No writer has been implemented for this type of resource.');
    }
}
