<?php

namespace Elixir\DI;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
trait ContainerUtilTrait 
{
    /**
     * @param string $key
     * @param array $arguments
     * @return mixed
     */
    public function withArgs($key, array $arguments = [])
    {
        return $this->get($key, ['arguments' => $arguments]);
    }
    
    /**
     * @param string $key
     * @param mixed $value 
     * @param array $options 
     */
    public function share($key, $value, array $options = [])
    {
        $options['share'] = true;
        $this->set($key, $value, $options);
    }
    
    /**
     * @param string $key
     * @param string $class 
     * @param array $arguments 
     */
    public function lazy($key, $class, array $arguments = [])
    {
        $value = function($container) use ($class, $arguments)
        {
            return $container->resolve($class, $arguments);
        };
        
        $this->set($key, $value);
    }
    
    /**
     * @param mixed $value
     * @return \Closure
     */
    public function wrap($value)
    {
        return function() use ($value)
        {
            return $value;
        };
    }
    
    /**
     * @param string $class
     * @param array $arguments
     * @return mixed
     */
    public function resolve($class, array $arguments = [])
    {
        $reflector = new \ReflectionClass($class);
            
        if (!$reflector->isInstantiable())
        {
            throw new \RuntimeException(sprintf('Class %s is not instanciable.', $class));
        }
        
        $constructor = $reflector->getConstructor();

        if (null === $constructor)
        {
            return new $class();
        }

        $dependencies = $this->getClassDependencies($constructor->getParameters(), $arguments);
        return $reflector->newInstanceArgs($dependencies);
    }
    
    /**
     * @param array $dependencies
     * @param array $arguments
     * @return array
     * @throws \RuntimeException
     * @throws \Exception
     */
    protected function getClassDependencies(array $dependencies, array $arguments = [])
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
                    else
                    {
                        throw  new \RuntimeException(sprintf('No default value available for parameter %s.', $name));
                    }
                }
                else
                {
                    try 
                    {
                        $parameters[] = $this->get($class->name, ['resolve' => true, 'throw' => true]);
                    } 
                    catch (\Exception $e)
                    {
                        if ($dependency->isOptional())
			{
                            $parameters[] = $dependency->getDefaultValue();
			}
                        else
                        {
                            throw $e;
                        }
                    }
                }
            }
        }
        
        return $parameters;
    }
}
