<?php

namespace Elixir\Config\Loader;

use Elixir\Config\Loader\Arr;
use Elixir\Config\Loader\INI;
use Elixir\Config\Loader\JSON;
use Elixir\Config\Loader\XML;

class LoaderFactory
{
    /**
     * @param mixed $pConfig
     * @param array $pOptions
     * @return LoaderInterface
     * @throws \InvalidArgumentException
     */
    public static function create($pConfig, array $pOptions = [])
    {
        $environment = isset($pOptions['environment']) ? $pOptions['environment'] : null;
        $strict = isset($pOptions['strict']) ? $pOptions['strict'] : false;
        
        if(is_array($pConfig) || strstr($pConfig, '.php')) 
        {
            return new Arr($environment, $strict);
        }
        else if(strstr($pConfig, '.xml')) 
        {
            return new XML($environment, $strict);
        }
        else if(strstr($pConfig, '.ini')) 
        {
            return new INI($environment, $strict);
        }
        else if(strstr($pConfig, '.json')) 
        {
            return new JSON($environment, $strict);
        }
        else
        {
            throw new \InvalidArgumentException('No loader has been implemented for this type of resource.');
        }
    }
}

