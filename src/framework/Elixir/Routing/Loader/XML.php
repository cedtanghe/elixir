<?php

namespace Elixir\Routing\Loader;

use Elixir\Routing\Collection;
use Elixir\Routing\Route;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class XML implements LoaderInterface
{
    /**
     * @see LoaderInterface::load()
     */
    public function load($pConfig, Collection $pCollection = null)
    {
        if(is_file($pConfig))
        {
            $pConfig = simplexml_load_file($pConfig);
        }
        
        $collection = new Collection();
        
        foreach($pConfig->routes->route as $route)
        {
            $name = (string)$route['name'];
            $priority = isset($route['priority']) ? (int)$route['priority'] : 0;
            $regex = (string)$route->regex;
            
            $parameters = array();
            $options = array();
            
            if(isset($route->parameters))
            {
                foreach($route->parameters->children() as $key => $value)
                {
                    $parameters[$key] = (string)$value;
                }
            }
            
            if(isset($route->options))
            {
                foreach($route->options->children() as $key => $value)
                {
                    switch($key)
                    {
                        case Route::METHOD:
                            if(count($value->method) == 0)
                            {
                                $options[Route::METHOD] = (string)$value;
                                continue;
                            }

                            $methods = array();

                            foreach($value->method as $method)
                            {
                                $methods[] = (string)$method;
                            }

                            $options[Route::METHOD] = $methods;
                        break;
                        case Route::REPLACEMENTS:
                            $replacements = array();
                            
                            foreach($value->children() as $k => $v)
                            {
                                $replacements[$k] = (string)$v;
                            }
                            
                            $options[Route::REPLACEMENTS] = $replacements;
                        break;
                        default:
                            $options[$key] = (string)$value;
                        break;
                    }
                }
            }
            
            $collection->add($name, new Route($regex, $parameters, $options), $priority);
        }
        
        if(isset($pConfig->{self::GLOBALS}))
        {
            $xml = $pConfig->{self::GLOBALS};
            $routes = $collection->gets();
            
            if(isset($xml->parameters))
            {
                foreach($xml->parameters->children() as $key => $value)
                {
                    foreach($routes as $route)
                    {
                        if(!$route->hasParameter($key))
                        {
                            $route->setParameter($key, (string)$value);
                        }
                    }
                }
            }
            
            if(isset($xml->options))
            {
                foreach($xml->options->children() as $key => $value)
                {
                    foreach($routes as $route)
                    {
                        switch($key)
                        {
                            case Route::METHOD:
                                if(!$route->hasOption(Route::METHOD))
                                {
                                    if(count($value->method) == 0)
                                    {
                                        $route->setOption(Route::METHOD, (string)$value);
                                        continue;
                                    }

                                    $methods = array();

                                    foreach($value->method as $method)
                                    {
                                        $methods[] = (string)$method;
                                    }

                                    $route->setOption(Route::METHOD, $methods);
                                }
                            break;
                            case Route::REPLACEMENTS:
                                $replacements = array();

                                foreach($value->children() as $k => $v)
                                {
                                    $replacements[$k] = (string)$v;
                                }

                                $route->setOption(Route::REPLACEMENTS, 
                                                  array_merge($replacements,
                                                              $route->getOption(Route::REPLACEMENTS, array())));
                            break;
                            default:
                                if(!$route->hasOption($key))
                                {
                                    $route->setOption($key, (string)$value);
                                }
                            break;
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