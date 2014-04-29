<?php

namespace Elixir\Routing\Loader;

use Elixir\Config\Loader\LoaderInterface;
use Elixir\Routing\Loader\Arr;
use Elixir\Routing\Loader\JSON;
use Elixir\Routing\Loader\XML;

class LoaderFactory
{
    /**
     * @param mixed $pConfig
     * @return LoaderInterface
     * @throws \InvalidArgumentException
     */
    public static function create($pConfig)
    {
        if(is_array($pConfig) || strstr($pConfig, '.php')) 
        {
            return new Arr();
        }
        else if(strstr($pConfig, '.xml')) 
        {
            return new XML();
        }
        else if(strstr($pConfig, '.json')) 
        {
            return new JSON();
        }
        else
        {
            throw new \InvalidArgumentException('No loader has been implemented for this type of resource.');
        }
    }
}
