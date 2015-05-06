<?php

namespace Elixir\DI;

use Elixir\DI\ContainerEvent;
use Elixir\DI\ContainerInterface;
use Elixir\DI\ProviderInterface;
use Elixir\Dispatcher\DispatcherInterface;
use Elixir\Dispatcher\DispatcherTrait;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class Container implements ContainerInterface, DispatcherInterface
{
    use DispatcherTrait;

    /**
     * @var array 
     */
    protected $data = [];

    /**
     * @var array 
     */
    protected $tags = [];

    /**
     * @var array 
     */
    protected $aliases = [];
    
    /**
     * @var array 
     */
    protected $extenders = [];
    
    /**
     * @var array 
     */
    protected $contextual = [];
    
    /**
     * @var array
     */
    protected $resolvedStack = [];

    /**
     * @var array 
     */
    protected $shared = [];

    /**
     * @var array 
     */
    protected $providers = [];
    
    /**
     * @see ContainerInterface::addProvider()
     */
    public function addProvider(ProviderInterface $provider)
    {
        if (!$pProvider->isDeferred())
        {
            $pProvider->register($this);
        } 
        else 
        {
            $this->providers[] = $provider;
        }
    }

    /**
     * @return array
     */
    public function getProviders() 
    {
        return $this->providers;
    }
    

    /**
     * @see ContainerInterface::has()
     */
    public function has($key) 
    {
        if (isset($this->aliases[$key]))
        {
            $key = $this->aliases[$key];
        }

        if (isset($this->data[$key]))
        {
            return true;
        }

        $i = count($this->providers);

        while ($i--)
        {
            $provider = $this->providers[$i];

            if (in_array($key, $provider->provides()))
            {
                $provider->register($this);
                array_splice($this->providers, $i, 1);

                return $this->has($key);
            }
        }

        return false;
    }
    
    /**
     * @see ContainerInterface::get()
     * @throws \LogicException
     */
    public function get($key, array $options = [], $default = null)
    {
        $options += ['throw' => true, 'rebuild' => false];
        $fire = false;
        
        if ($this->has($key))
        {
            if (isset($this->aliases[$key]))
            {
                $key = $this->aliases[$key];
            }

            $value = $this->data[$key]['value'];
            
            if (!$this->data[$key]['shared'] || !array_key_exists($key, $this->shared) || $options['rebuild'])
            {
                $arguments = [$this];
                
                if (isset($options['arguments']) && count($options['arguments']) > 0)
                {
                    if ($this->data[$key]['shared']) 
                    {
                        throw new \LogicException(sprintf('A service (%s) with arguments can not be shared.', $key));
                    }

                    $arguments = array_merge($arguments, $options['arguments']);
                }
                
                if (is_callable($value)) 
                {
                    $value = call_user_func_array($value, $arguments);
                }
                else
                {
                    try
                    {
                        $available = isset($options['resolver_provider']) ? $options['resolver_provider'] : [];
                        $value = $this->resolveClass($key, $available);
                    }
                    catch (\RuntimeException $e)
                    {
                        if ($options['throw'])
                        {
                            throw $e;
                        }
                        else
                        {
                            return is_callable($default) ? call_user_func($default) : $default;
                        }
                    }
                }
                
                $fire = true;
            }
            else
            {
                $value = $this->shared[$key];
            }
        } 
        else
        {
            try
            {
                $available = isset($options['resolver_provider']) ? $options['resolver_provider'] : [];
                $value = $this->resolveClass($key, $available);
            } 
            catch (\RuntimeException $e)
            {
                if ($options['throw'])
                {
                    throw $e;
                }
                else
                {
                    return is_callable($default) ? call_user_func($default) : $default;
                }
            }
            
            $fire = true;
        }

        if (isset($this->extenders[$key]))
        {
            foreach ($this->extenders[$key] as $extender)
            {
                $value = call_user_func_array($extender, [$value, $this]);
            }
        }

        if ($this->data[$key]['shared'])
        {
            unset($this->extenders[$key]);
            $this->shared[$key] = $value;
        }
        
        if ($fire)
        {
            $this->dispatch(new ContainerEvent(ContainerEvent::RESOLVED, ['service' => $key]));
        }

        return $value;
    }
    
    /**
     * @param string $when
     * @param string $needs
     * @param mixed $implementation
     */
    public function addContextualBinding($when, $needs, $implementation)
    {
        $this->contextual[$when][$needs] = $implementation;
    }
    
    /**
     * @param string $needs
     * @return mixed
     */
    protected function getContextualObject($needs)
    {
        $when = end($this->resolvedStack);
        
        if (isset($this->contextual[$when][$needs]))
        {
            return $this->contextual[$when][$needs];
        }
        
        return null;
    }
    
    /**
     * @see ContainerInterface::resolve()
     */
    public function resolve($callback, array $available = [])
    {
        if (is_string($callback) && false === strpos($callback, '::'))
        {
            return $this->get(
                $callback, 
                [
                    'throw' => true,
                    'resolver_provider' => $available
                ]
            );
        }
        else
        {
            return $this->resolveCallable($callback, $available);
        }
    }
    
    /**
     * @param string $callback
     * @param array $available
     * @return array
     * @throws \RuntimeException
     */
    protected function resolveCallable($callback, array $available = [])
    {
        if (is_string($callback) && false !== strpos($callback, '::'))
        {
            $callback = explode('::', $callback);
        }
        
        if (is_array($callback))
        {
            $reflector = new ReflectionMethod($callback[0], $callback[1]);
        }
        else
        {
            $reflector = new ReflectionFunction($callback);
        }
        
        return [
            $callback, 
            $this->getDependencies($constructor->getParameters(), $available)
        ];
    }
    
    /**
     * @param string $class
     * @param array $available
     * @return mixed
     * @throws \RuntimeException
     */
    protected function resolveClass($class, array $available = [])
    {
        $contextual = $this->getContextualObject($class);
        
        if (null !== $contextual)
        {
            if (is_callable($contextual))
            {
                return call_user_func_array($contextual, [$this]);
            }
            
            $class = $contextual;
        }
        
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

        $this->resolvedStack[] = $class;
        $arguments = $this->getDependencies($constructor->getParameters(), $available);
        array_pop($this->resolvedStack);
        
        return $reflector->newInstanceArgs($arguments);
    }

    /**
     * @param array $dependencies
     * @param array $available
     * @return array
     * @throws \RuntimeException
     * @throws \Exception
     */
    protected function getDependencies(array $dependencies, array $available = [])
    {
        $arguments = [];
        
        foreach ($dependencies as $dependency)
        {
            $name = $dependency->getName();
            
            if (array_key_exists($name, $available))
            {
                $arguments[] = $available[$name];
            }
            else
            {
                $class = $dependency->getClass();
                
                if (null === $class)
                {
                    if ($dependency->isDefaultValueAvailable())
                    {
                        $arguments[] = $dependency->getDefaultValue();
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
                        $arguments[] = $this->get($class->name, ['throw' => true]);
                    } 
                    catch (\Exception $e)
                    {
                        if ($dependency->isOptional())
			{
                            $arguments[] = $dependency->getDefaultValue();
			}
                        else
                        {
                            throw $e;
                        }
                    }
                }
            }
        }
        
        return $arguments;
    }

    /**
     * @see ContainerInterface::set()
     */
    public function bind($key, $value, array $options = [])
    {
        $options += [
            'shared' => false,
            'tags' => [],
            'aliases' => [],
            'extenders' => []
        ];
        
        if (isset($this->aliases[$key])) 
        {
            $key = $this->aliases[$key];
        }
        
        $this->data[$key] = ['shared' => $options['shared'], 'value' => $value];
        
        foreach ($options['extenders'] as $extender)
        {
            $this->extend($key, $extender);
        }
        
        $this->dispatch(new ContainerEvent(ContainerEvent::BINDED, ['service' => $key]));
        
        foreach ($options['tags'] as $tag)
        {
            $this->addTag($key, $tag);
        }
        
        foreach ($options['aliases'] as $alias)
        {
            $this->addAlias($key, $alias);
        }
    }
    
    /**
     * @see ContainerInterface::share()
     */
    public function share($key, $value, array $options = [])
    {
        $options['share'] = true;
        $this->bind($key, $value, $options);
    }

    /**
     * @see ContainerInterface::unbind()
     */
    public function unbind($key) 
    {
        if (isset($this->aliases[$key])) 
        {
            $key = $this->aliases[$key];
        }

        foreach ($this->aliases as $alias => $k)
        {
            if ($key == $k) 
            {
                unset($this->aliases[$alias]);
            }
        }

        foreach ($this->tags as $tag => &$ks) 
        {
            $i = count($ks);

            while ($i--) 
            {
                if ($ks[$i] == $key)
                {
                    array_splice($ks, $i, 1);
                    break;
                }
            }
            
            if (count($ks) == 0)
            {
                unset($this->tags[$tag]);
            }
        }

        unset($this->extenders[$key]);
        unset($this->data[$key]);
    }

    /**
     * @see ContainerInterface::all()
     */
    public function all(array $options = []) 
    {
        $options += [
            'raw' => false,
            'providers' => false,
            'contextual_bindings' => false
        ];
        
        $services = [];
        $data = [];
        
        if ($options['providers'] || $options['contextual_bindings'])
        {
            $data['services'] = &$services;
            
            if ($options['providers'])
            {
                $data['providers'] = $this->providers;
            }
            
            if ($options['contextual_bindings'])
            {
                $data['contextual_bindings'] = $this->contextual;
            }
        }
        else
        {
            $data = &$services;
        }
        
        $keys = array_keys($this->data);
        
        foreach ($keys as $key)
        {
            $services[$key] = $raw ? $this->raw($key) : $this->get($key, ['throw' => false]);
        }
        
        return $data;
    }

    /**
     * @see ContainerInterface::replace()
     */
    public function replace(array $data)
    {
        if (isset($data['services']))
        {
            $this->data = [];

            foreach ($data['services'] as $key => $service) 
            {
                if (is_array($service) && isset($service['options']))
                {
                    $value = $service['value'];
                    $options = $service['options'];
                } 
                else 
                {
                    $value = $service;
                    $options = [];
                }

                $this->bind($key, $value, $options);
            }
        }
        
        if (isset($data['providers']))
        {
            $this->providers = [];

            foreach ($data['providers'] as $provider)
            {
                $this->addProvider($provider);
            }
        }
    }

    /**
     * @see ContainerInterface::addAlias()
     */
    public function addAlias($key, $alias)
    {
        if ($this->has($key)) 
        {
            if (isset($this->aliases[$key]))
            {
                $key = $this->aliases[$key];
            }

            $this->aliases[$alias] = $key;
            $this->dispatch(new ContainerEvent(ContainerEvent::ALIASED, ['service' => $key, 'alias' => $alias]));
        }
    }

    /**
     * @see ContainerInterface::addTag()
     */
    public function addTag($key, $tag) 
    {
        if ($this->has($key))
        {
            if (isset($this->aliases[$key]))
            {
                $key = $this->aliases[$key];
            }
            
            if (!isset($this->tags[$tag]) || !in_array($key, $this->tags[$tag]))
            {
                $this->tags[$tag][] = $key;
                $this->dispatch(new ContainerEvent(ContainerEvent::TAGGED, ['service' => $key, 'tag' => $tag]));
            }
        }
    }

    /**
     * @see ContainerInterface::findByTag()
     */
    public function findByTag($tag)
    {
        foreach ($this->providers as $providers)
        {
            $providers->register($this);
        }
        
        $services = [];
        
        if (isset($this->tags[$tag]))
        {
            foreach ($this->tags[$tag] as $key)
            {
                $services[$key] = $this->get($key, ['throw' => false]);
            }
        }

        return $services;
    }

    /**
     * @see ContainerInterface::raw()
     * @throws \InvalidArgumentException
     */
    public function raw($key)
    {
        if (!$this->has($key))
        {
            throw new \InvalidArgumentException(sprintf('Service "%s" is not defined.', $key));
        }

        if (isset($this->aliases[$key]))
        {
            $key = $this->aliases[$key];
        }
        
        $data = $this->data[$key];
        $data['extenders'] = isset($this->extenders[$key]) ? $this->extenders[$key] : [];
        $data['aliases'] = [];
        $data['tags'] = [];
        
        foreach ($this->aliases as $alias => $k)
        {
            if ($key == $k) 
            {
                $data['aliases'][] = $alias;
            }
        }

        foreach ($this->tags as $tag => $ks) 
        {
            if (in_array($key, $ks))
            {
                $data['tags'][] = $tag;
            }
        }

        return $data;
    }
    
    /**
     * @param string $key
     * @return boolean
     */
    public function isShared($key)
    {
        if ($this->has($key))
        {
            if (isset($this->aliases[$key]))
            {
                $key = $this->aliases[$key];
            }
            
            return $this->data[$key]['shared'];
        }
        
        return false;
    }
    
    /**
     * @param string $key
     * @return boolean
     */
    public function isTagged($key)
    {
        if ($this->has($key))
        {
            if (isset($this->aliases[$key]))
            {
                $key = $this->aliases[$key];
            }
            
            foreach ($this->tags as $tag => $keys) 
            {
                if (in_array($key, $keys))
                {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * @param string $key
     * @return boolean
     */
    public function isAliased($key)
    {
        if ($this->has($key))
        {
            if (isset($this->aliases[$key]))
            {
                $key = $this->aliases[$key];
            }
            
            foreach ($this->aliases as $alias => $k)
            {
                if ($key == $k) 
                {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     *  @see ContainerInterface::extend()
     */
    public function extend($key, callable $value) 
    {
        if (isset($this->aliases[$key]))
        {
            $key = $this->aliases[$key];
        }
        
        $this->extenders[$key][] = $value;
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
     * @see ContainerInterface::merge()
     */
    public function merge($data) 
    {
        if ($data instanceof self) 
        {
            $all = $data->all([
                'raw' => true,
                'providers' => true,
                'contextual_bindings' => true
            ]);
            
            $data = $gets['services'];
            
            foreach ($all['providers'] as $provider)
            {
                $this->addProvider($provider);
            }
            
            $this->contextual = array_merge($this->contextual, $all['contextual_bindings']);
        }
        
        foreach ($data as $key => $service)
        {
            if (is_array($service) && isset($service['options']))
            {
                $value = $service['value'];
                $options = $service['options'];
            } 
            else 
            {
                $value = $service;
                $options = [];
            }

            $this->bind($key, $value, $options);
        }
    }

    /**
     * @ignore
     */
    public function __debugInfo() 
    {
        $services = $this->all(['raw' => true]);
        $provides = [];
        $contextualBindings = [];

        foreach ($services as $key => &$data) 
        {
            unset($data['value']);
        }
        
        foreach ($this->providers as $provider)
        {
            $provides = array_merge($provides, $provider->provides());
        }
        
        foreach ($this->contextual as $key => $value)
        {
            $contextualBindings[$key] = array_keys($value);
        }

        return [
            'services' => $services,
            'aliases' => $this->aliases,
            'tags' => $this->tags,
            'provides' => $provides,
            'extenders' => array_keys($this->extenders),
            'contextual_bindings' => $contextualBindings
        ];
    }
}
