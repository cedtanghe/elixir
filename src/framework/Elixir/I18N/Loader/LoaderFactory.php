<?php

namespace Elixir\I18N\Loader;

use Elixir\I18N\Loader\Arr;
use Elixir\I18N\Loader\CSV;
use Elixir\I18N\Loader\Gettext;
use Elixir\I18N\Loader\JSON;
use Elixir\I18N\Loader\LoaderInterface;

class LoaderFactory
{
    /**
     * @param mixed $pResource
     * @return LoaderInterface
     * @throws \InvalidArgumentException
     */
    public static function create($pResource)
    {
        if(is_array($pResource) || strstr($pResource, '.php')) 
        {
            return new Arr();
        }
        else if(strstr($pResource, '.mo')) 
        {
            return new Gettext();
        }
        else if(strstr($pResource, '.csv')) 
        {
            return new CSV();
        }
        else if(strstr($pResource, '.json')) 
        {
            return new JSON();
        }
        else
        {
            throw new \InvalidArgumentException('No loader has been implemented for this type of resource.');
        }
    }
}
