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
    protected $shared = [];

    /**
     * @var array 
     */
    protected $providers = [];

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
        if ($this->has($key))
        {
            $options = array_merge(
                [
                    'resolve' => true,
                    'throw' => true
                ],
                $options
            );
            
            if (isset($this->aliases[$key]))
            {
                $key = $this->aliases[$key];
            }

            $value = $this->data[$key]['value'];
            $arguments = [$this];

            if (isset($options['arguments']) && count($options['arguments']) > 0)
            {
                if ($this->data[$key]['type'] != self::BIND) 
                {
                    throw new \LogicException(sprintf('"%s" object must be simple "bind" type.', $key));
                }

                $arguments[] = $options['arguments'];
            }

            if (!$this->data[$key]['shared'] || !array_key_exists($key, $this->shared))
            {
                if (is_callable($value)) 
                {
                    $value = call_user_func_array($value, $arguments);
                }
            }
            else
            {
                $value = $this->shared[$key];
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
            
            return $value;
        } 
        else if ($options['resolve']) 
        {
            try
            {
                $value = $this->resolve($key);
                
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
                
                return $value;
            } 
            catch (\RuntimeException $e)
            {
                if ($options['throw'])
                {
                    throw $e;
                }
            }
        }

        return is_callable($default) ? call_user_func($default) : $default;
    }
    
    public function resolve($class)
    {
        $reflector = new \ReflectionClass($class);
            
        if (!$reflector->isInstantiable())
        {
            throw new \RuntimeException('');
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
                    else
                    {
                        throw  new \RuntimeException('');
                    }
                }
                else
                {
                    try 
                    {
                        $parameters[] = $this->get($class->name, ['resolve' => true]);
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

    /**
     * @see ContainerInterface::set()
     * @throws \LogicException
     */
    public function set($pKey, $pValue, array $pOptions = []) {
        switch ($this->lockMode) {
            case self::READ_ONLY:
                throw new \LogicException('The dependency injection container is read only.');
                break;
            case self::IGNORE_IF_ALREADY_EXISTS:
            case self::THROW_IF_ALREADY_EXISTS:
                if ($this->has($pKey)) {
                    if ($this->lockMode == self::THROW_IF_ALREADY_EXISTS) {
                        throw new \LogicException(sprintf('Service "%s" has already been defined.', $pKey));
                    }

                    return;
                }
                break;
        }

        if (!isset($pOptions['type'])) {
            $pOptions['type'] = self::BIND;
        }

        $type = $pOptions['type'];
        $tags = (array) (isset($pOptions['tags']) ? $pOptions['tags'] : []);
        $aliases = (array) (isset($pOptions['aliases']) ? $pOptions['aliases'] : []);

        switch ($type) {
            case self::SHARED:
                $this->singleton($pKey, $pValue, $tags, $aliases);
                break;
            case self::WRAP:
                $this->wrap($pKey, $pValue, $tags, $aliases);
                break;
            case self::EXTEND:
                $this->extend($pKey, $pValue);
                break;
            default:
                $this->bind($pKey, $pValue, $tags, $aliases);
                break;
        }
    }

    /**
     * @see ContainerInterface::remove()
     */
    public function remove($pKey) {
        if (isset($this->aliases[$pKey])) {
            $pKey = $this->aliases[$pKey];
        }

        foreach ($this->aliases as $key => $value) {
            if ($value == $pKey) {
                unset($this->aliases[$key]);
            }
        }

        foreach ($this->tags as $tag => &$services) {
            $i = count($services);

            while ($i--) {
                if ($services[$i] == $pKey) {
                    array_splice($services, $i, 1);
                    break;
                }
            }

            if (count($services) == 0) {
                unset($this->tags[$tag]);
            }
        }

        unset($this->data[$pKey]);
    }

    /**
     * @see ContainerInterface::all()
     */
    public function gets(array $pOptions = []) {
        $result = [];
        $data = [];
        $raw = isset($pOptions['raw']) && $pOptions['raw'];
        $providers = isset($pOptions['providers']) && $pOptions['providers'];

        if ($providers) {
            $result['data'] = &$data;
            $result['providers'] = $this->providers;
        } else {
            $result = &$data;
        }

        foreach ($this->data as $key => $value) {
            $data[$key] = $raw ? $this->raw($key) : $this->get($key);
        }

        return $result;
    }

    /**
     * @see ContainerInterface::sets()
     */
    public function sets(array $pData, array $pGlobalOptions = []) {
        if (isset($pData['data'])) {
            $this->data = [];

            foreach ($pData['data'] as $key => $config) {
                if (is_array($config)) {
                    $value = $config['value'];
                    $options = isset($config['options']) ? $config['options'] : [];
                } else {
                    $value = $config;
                    $options = [];
                }

                $this->set($key, $value, array_merge($options, $pGlobalOptions));
            }
        }

        if (isset($pData['providers'])) {
            $this->providers = [];

            foreach ($pData['providers'] as $provider) {
                $this->addProvider($pProvider);
            }
        }
    }

    /**
     * @see ContainerInterface::addAlias()
     */
    public function addAlias($pKey, $pAlias) {
        if ($this->has($pKey)) {
            if (isset($this->aliases[$pKey])) {
                $pKey = $this->aliases[$pKey];
            }

            $this->aliases[$pAlias] = $pKey;
        }
    }

    /**
     * @see ContainerInterface::addTag()
     */
    public function addTag($pKey, $pTag) {
        if ($this->has($pKey)) {
            if (isset($this->aliases[$pKey])) {
                $pKey = $this->aliases[$pKey];
            }

            $this->tags[$pTag][] = $pKey;
            $this->tags[$pTag] = array_unique($this->tags[$pTag]);
        }
    }

    /**
     * @see ContainerInterface::findByTag()
     */
    public function findByTag($pTag, array $pArguments = [], $pDefault = null) {
        foreach ($this->providers as $providers) {
            $providers->register($this);
        }

        $services = isset($this->tags[$pTag]) ? $this->tags[$pTag] : [];
        $result = [];

        foreach ($services as $key) {
            $s = $this->get($key, ['arguments' => $pArguments, 'resolve' => false], null);

            if (null !== $s) {
                $result[$key] = $s;
            }
        }

        if (count($result) == 0) {
            return is_callable($pDefault) ? $pDefault() : $pDefault;
        }

        return $result;
    }

    /**
     * @see ContainerInterface::raw()
     * @throws \InvalidArgumentException
     */
    public function raw($pKey) {
        if (!$this->has($pKey)) {
            throw new \InvalidArgumentException(sprintf('Service "%s" is not defined.', $pKey));
        }

        if (isset($this->aliases[$pKey])) {
            $pKey = $this->aliases[$pKey];
        }

        $data = $this->data[$pKey];
        $data['aliases'] = [];
        $data['tags'] = [];

        foreach ($this->aliases as $key => $value) {
            if ($value == $pKey) {
                $data['aliases'][] = $key;
            }
        }

        foreach ($this->tags as $tag => $services) {
            if (in_array($pKey, $services)) {
                $data['tags'][] = $key;
            }
        }

        return $data;
    }

    /**
     * @param string $pKey
     * @param mixed $pValue
     * @param array $pOptions
     * @throws \LogicException
     */
    public function bind($pKey, $pValue, array $pOptions = []) {
        switch ($this->lockMode) {
            case self::READ_ONLY:
                throw new \LogicException('The dependency injection container is read only.');
                break;
            case self::IGNORE_IF_ALREADY_EXISTS:
            case self::THROW_IF_ALREADY_EXISTS:
                if ($this->has($pKey)) {
                    if ($this->lockMode == self::THROW_IF_ALREADY_EXISTS) {
                        throw new \LogicException(sprintf('Service "%s" has already been defined.', $pKey));
                    }

                    return;
                }
                break;
        }

        $this->data[$pKey] = ['type' => self::BIND, 'value' => $pValue];

        if (isset($pOptions['tags'])) {
            foreach ((array) $pOptions['tags'] as $tag) {
                $this->addTag($pKey, $tag);
            }
        }

        if (isset($pOptions['alias'])) {
            foreach ((array) $pOptions['alias'] as $alias) {
                $this->addAlias($pKey, $alias);
            }
        }

        $this->dispatch(new ContainerEvent(ContainerEvent::CREATED, ['name' => $pKey]));
    }

    /**
     * @param string $pKey
     * @param mixed $pValue
     * @param array $pOptions
     * @param mixed $pAliases
     * @throws \LogicException
     */
    public function singleton($pKey, $pValue, array $pOptions = []) {
        switch ($this->lockMode) {
            case self::READ_ONLY:
                throw new \LogicException('The dependency injection container is read only.');
                break;
            case self::IGNORE_IF_ALREADY_EXISTS:
            case self::THROW_IF_ALREADY_EXISTS:
                if ($this->has($pKey)) {
                    if ($this->lockMode == self::THROW_IF_ALREADY_EXISTS) {
                        throw new \LogicException(sprintf('Service "%s" has already been defined.', $pKey));
                    }

                    return;
                }
                break;
        }

        $value = function(self $pContainer) use ($pValue) {
            static $instance;

            if (null === $instance) {
                $instance = is_callable($pValue) ? $pValue($pContainer) : $pValue;
            }

            return $instance;
        };

        $this->data[$pKey] = ['type' => self::SHARED, 'value' => $value];

        if (isset($pOptions['tags'])) {
            foreach ((array) $pOptions['tags'] as $tag) {
                $this->addTag($pKey, $tag);
            }
        }

        if (isset($pOptions['alias'])) {
            foreach ((array) $pOptions['alias'] as $alias) {
                $this->addAlias($pKey, $alias);
            }
        }

        $this->dispatch(new ContainerEvent(ContainerEvent::CREATED, ['name' => $pKey]));
    }

    /**
     * @param string $pKey
     * @param callable $pValue
     * @param array $pOptions
     * @throws \LogicException
     */
    public function wrap($pKey, callable $pValue, array $pOptions = []) {
        switch ($this->lockMode) {
            case self::READ_ONLY:
                throw new \LogicException('The dependency injection container is read only.');
                break;
            case self::IGNORE_IF_ALREADY_EXISTS:
            case self::THROW_IF_ALREADY_EXISTS:
                if ($this->has($pKey)) {
                    if ($this->lockMode == self::THROW_IF_ALREADY_EXISTS) {
                        throw new \LogicException(sprintf('Service "%s" has already been defined.', $pKey));
                    }

                    return;
                }
                break;
        }

        $value = function() use ($pValue) {
            return $pValue;
        };

        $this->data[$pKey] = ['type' => self::WRAP, 'value' => $value];

        if (isset($pOptions['tags'])) {
            foreach ((array) $pOptions['tags'] as $tag) {
                $this->addTag($pKey, $tag);
            }
        }

        if (isset($pOptions['alias'])) {
            foreach ((array) $pOptions['alias'] as $alias) {
                $this->addAlias($pKey, $alias);
            }
        }

        $this->dispatch(new ContainerEvent(ContainerEvent::CREATED, ['name' => $pKey]));
    }

    /**
     * @param string $pKey
     * @param callable $pValue
     */
    public function extend($pKey, callable $pValue) {
        if (!$this->has($pKey)) {
            throw new \InvalidArgumentException(sprintf('Service "%s" is not defined.', $pKey));
        }

        if (isset($this->aliases[$pKey])) {
            $pKey = $this->aliases[$pKey];
        }

        $value = $this->data[$pKey]['value'];

        $service = function() use($value) {
            return is_callable($value) ? $value($this) : $value;
        };

        $type = $this->data[$pKey]['type'];

        $this->data[$pKey]['value'] = function(self $pContainer) use ($service, $pValue, $type) {
            if ($type == self::SHARED) {
                static $instance;

                if (null === $instance) {
                    $instance = $pValue($service(), $pContainer);
                }

                return $instance;
            }

            return $pValue($service(), $pContainer);
        };
    }

    /**
     * @see ContainerInterface::load()
     */
    public function addProvider(ProviderInterface $pProvider) {
        if (!$pProvider->isDeferred()) {
            $pProvider->register($this);
        } else {
            $this->providers[] = $pProvider;
        }
    }

    /**
     * @return array
     */
    public function getProviders() {
        return $this->providers;
    }

    /**
     * @see ContainerInterface::merge()
     * @throws \LogicException
     */
    public function merge($pData) {
        if ($pData instanceof self) {
            $gets = $pData->gets(['raw' => true, 'providers' => true]);
            $pData = $gets['data'];
        }

        $aliases = [];
        $tags = [];

        foreach ($pData as $key => &$value) {
            switch ($this->lockMode) {
                case self::READ_ONLY:
                    throw new \LogicException('The dependency injection container is read only.');
                    break;
                case self::IGNORE_IF_ALREADY_EXISTS:
                case self::THROW_IF_ALREADY_EXISTS:
                    if ($this->has($pKey)) {
                        if ($this->lockMode == self::THROW_IF_ALREADY_EXISTS) {
                            throw new \LogicException(sprintf('Service "%s" has already been defined.', $key));
                        }

                        unset($pData[$key]);
                        continue 2;
                    }
                    break;
            }

            if (isset($value['aliases'])) {
                foreach ($value['aliases'] as $alias) {
                    $aliases[$alias] = $key;
                }

                unset($value['aliases']);
            }

            if (isset($value['tags'])) {
                foreach ($value['tags'] as $tag) {
                    $tags[$tag][] = $key;
                }

                unset($value['tags']);
            }
        }

        $this->data = array_merge($this->data, $pData);

        if (isset($gets['providers'])) {
            foreach ($gets['providers'] as $provider) {
                $this->addProvider($provider);
            }
        }

        foreach ($aliases as $key => $value) {
            $this->addAlias($value, $key);
        }

        foreach ($tags as $tag => $services) {
            foreach ($services as $service) {
                $this->addTag($service, $tag);
            }
        }
    }

    /**
     * @ignore
     */
    public function __debugInfo() {
        $data = $this->gets(['raw' => true, 'providers' => true]);
        $provides = [];

        foreach ($data['data'] as $key => &$config) {
            unset($config['value']);
        }

        foreach ($data['providers'] as $provider) {
            $provides = array_merge($provides, $provider->provides());
        }

        return [
            'data' => $data['data'],
            'aliases' => $this->aliases,
            'tags' => $this->tags,
            'provides' => $provides,
        ];
    }
}
