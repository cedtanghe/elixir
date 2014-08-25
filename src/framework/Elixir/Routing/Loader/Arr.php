<?php

namespace Elixir\Routing\Loader;

use Elixir\Routing\Collection;
use Elixir\Routing\Loader\LoaderInterface;
use Elixir\Routing\Route;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Arr implements LoaderInterface
{
    /**
     * @see LoaderInterface::load()
     */
    public function load($pConfig, Collection $pCollection = null)
    {
        if(is_file($pConfig))
        {
            $pConfig = include $pConfig;
        }
        
        $collection = new Collection();
        
        foreach($pConfig as $key => $value)
        {
            if($key == self::GLOBALS)
            {
                continue;
            }
            
            $name = $key;
            $priority = isset($value['priority']) ? $value['priority'] : 0;
            $regex = $value['regex'];
            $parameters = isset($value['parameters']) ? $value['parameters'] : [];
            $options = isset($value['options']) ? $value['options'] : [];
            
            $collection->add($name, new Route($regex, $parameters, $options), $priority);
        }
        
        if(isset($pConfig[self::GLOBALS]))
        {
            $data = $pConfig[self::GLOBALS];
            $routes = $collection->gets();
            
            if(isset($data['parameters']))
            {
                foreach($data['parameters'] as $key => $value)
                {
                    foreach($routes as $route)
                    {
                        if(!$route->hasParameter($key))
                        {
                            $route->setParameter($key, $value);
                        }
                    }
                }
            }

            if(isset($data['options']))
            {
                foreach($data['options'] as $key => $value)
                {
                    foreach($routes as $route)
                    {
                        if($key === Route::REPLACEMENTS)
                        {
                            $replacements = [];

                            foreach($value as $k => $v)
                            {
                                $replacements[$k] = $v;
                            }
                            
                            $route->setOption(Route::REPLACEMENTS, 
                                              array_merge($replacements,
                                                          $route->getOption(Route::REPLACEMENTS, [])));
                        }
                        else if(!$route->hasOption($key))
                        {
                            $route->setOption($key, $value);
                        }
                    }
                }
            }
        }
        
        if(null !== $pCollection)
        {
            $pCollection->merge($collection);
            return $pCollection;
        }
        
        return $collection;
    }
}
