<?php

namespace Elixir\DI;

use Elixir\DI\ContainerInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Resolver
{
    /**
     * @var ContainerInterface 
     */
    protected $container;
    
    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) 
    {
        $this->container = $container;
    }
    
    /**
     * @param string $service
     * @param array $arguments
     * @return mixed
     */
    public function build($service, array $arguments = [])
    {
        if (strpos($service, '::') !== false)
        {
            $callback = explode('::', $callback);
            $reflector = new \ReflectionMethod($callback[0], $callback[1]);
        }
        
        $reflector = new \ReflectionClass($service);
            
        if (!$reflector->isInstantiable())
        {
            // Todo Error
        }

        $object = $reflector->getConstructor();

        if (null === $object)
        {
            return new $object();
        }

        $dependencies = $this->getDependencies($object->getParameters(), $arguments);
        return $reflector->newInstanceArgs($dependencies);
    }
    
    protected function getDependencies($dependencies, array $arguments = [])
    {
        $parameters = [];
        
        foreach ($dependencies as $dependency)
        {
            $name = $dependency->getName();
            
            if (array_key_exists($name, $arguments))
            {
                $parameters[] = $arguments[$name];
            }
            else
            {
                $class = $dependency->getClass();
                
                if (null === $class)
                {
                    if ($dependency->isDefaultValueAvailable())
                    {
                        $parameters[] = $dependency->getDefaultValue();
                    }
                    
                    // Todo error
                }
                else
                {
                    try 
                    {
                        $parameters[] = $this->container->get($class->name, [ 'resolve' => true]);
                    } 
                    catch (\Exception $e)
                    {
                        if ($dependency->isOptional())
			{
                            $parameters[] = $dependency->getDefaultValue();
                            continue;
			}
                        
                        throw $e;
                    }
                }
            }
        }
        
        return $parameters;
    }
}
