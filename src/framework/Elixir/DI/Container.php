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
    protected $_data = [];
    
    /**
     * @var array 
     */
    protected $_aliases = [];
    
    /**
     * @var array 
     */
    protected $_providers = [];
    
    /**
     * @var string 
     */
    protected $_lockMode = self::UNLOCKED;
    
   /**
     * @see ContainerInterface::setLockMode()
     */
    public function setLockMode($pValue)
    {
        $this->_lockMode = $pValue;
    }
    
    /**
     * @see ContainerInterface::getLockMode()
     */
    public function getLockMode()
    {
        return $this->_lockMode;
    }
    
    /**
     * @see ContainerInterface::has()
     */
    public function has($pKey)
    {
        if(isset($this->_aliases[$pKey]))
        {
            $pKey = $this->_aliases[$pKey];
        }
        
        if(isset($this->_data[$pKey]))
        {
            return true;
        }
        
        $i = count($this->_providers);
        
        while ($i--)
        {
            $provider = $this->_providers[$i];
            
            if(in_array($pKey, $provider->provides()))
            {
                $provider->register($this);
                array_splice($this->_providers, $i, 1);
                
                return $this->has($pKey);
            }
        }
        
        return false;
    }
    
    /**
     * @see ContainerInterface::get()
     * @throws \LogicException
     */
    public function get($pKey, array $pOptions = [], $pDefault = null)
    {
        if($this->has($pKey))
        {
            if(isset($this->_aliases[$pKey]))
            {
                $pKey = $this->_aliases[$pKey];
            }
            
            $data = $this->_data[$pKey]['value'];
            $arguments = [$this];
            
            if(isset($pOptions['arguments']) && count($pOptions['arguments']) > 0)
            {
                if($this->_data[$pKey]['type'] != self::BIND)
                {
                    throw new \LogicException(sprintf('"%s" service must be simple "bind" type.', $pKey));
                }
                
                $arguments[] = $pOptions['arguments'];
            }
            
            if(is_callable($data))
            {
                return call_user_func_array($data, $arguments);
            }

            return $data;
        }
        else
        {
            // Todo resolve
        }
        
        return is_callable($pDefault) ? $pDefault() : $pDefault;
    }

    /**
     * @see ContainerInterface::set()
     * @throws \LogicException
     */
    public function set($pKey, $pValue, array $pOptions = [])
    {
        switch($this->_lockMode)
        {
            case self::READ_ONLY:
                throw new \LogicException('The dependency injection container is read only.');
            break;
            case self::IGNORE_IF_ALREADY_EXISTS:
            case self::THROW_IF_ALREADY_EXISTS:
                if($this->has($pKey))
                {
                    if($this->_lockMode == self::THROW_IF_ALREADY_EXISTS)
                    {
                        throw new \LogicException(sprintf('Service "%s" has already been defined.', $pKey));
                    }
                    
                    return;
                }
            break;
        }
        
        if(!isset($pOptions['type']))
        {
            $pOptions['type'] = self::BIND;
        }
        
        $type = $pOptions['type'];
        $tags = (array)(isset($pOptions['tags']) ? $pOptions['tags'] : []);
        $aliases = (array)(isset($pOptions['aliases']) ? $pOptions['aliases'] : []);
        
        switch($type)
        {
            case self::SINGLETON:
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
    public function remove($pKey)
    {
        if(isset($this->_aliases[$pKey]))
        {
            $pKey = $this->_aliases[$pKey];
        }
        
        foreach($this->_aliases as $key => $value)
        {
            if($value == $pKey)
            {
                unset($this->_aliases[$key]);
            }
        }
        
        unset($this->_data[$pKey]);
    }
    
    /**
     * @see ContainerInterface::all()
     */
    public function gets(array $pOptions = [])
    {
        $result = [];
        $data = [];
        $raw = isset($pOptions['raw']) && $pOptions['raw'];
        $providers = isset($pOptions['providers']) && $pOptions['providers'];
        
        if($providers)
        {
            $result['data'] = &$data;
            $result['providers'] = $this->_providers;
        }
        else
        {
            $result = &$data;
        }
        
        foreach($this->_data as $key => $value)
        {
            $data[$key] = $raw ? $this->raw($key) : $this->get($key);
        }

        return $result;
    }
    
    /**
     * @see ContainerInterface::sets()
     */
    public function sets(array $pData, array $pGlobalOptions = [])
    {
        if(isset($pData['data']))
        {
            $this->_data = [];
            
            foreach($pData['data'] as $key => $config)
            {
                if(is_array($config))
                {
                    $value = $config['value'];
                    $options = isset($config['options']) ? $config['options'] : [];
                }
                else
                {
                    $value = $config;
                    $options = [];
                }
                
                $this->set($key, $value, array_merge($options, $pGlobalOptions));
            }
        }
        
        if(isset($pData['providers']))
        {
            $this->_providers = [];
            
            foreach($pData['providers'] as $provider)
            {
                $this->addProvider($pProvider);
            }
        }
    }
    
    /**
     * @see ContainerInterface::addAlias()
     */
    public function addAlias($pKey, $pAlias)
    {
        if($this->has($pKey))
        {
            if(isset($this->_aliases[$pKey]))
            {
                $pKey = $this->_aliases[$pKey];
            }
            
            $this->_aliases[$pAlias] = $pKey;
            $this->dispatch(new ContainerEvent(ContainerEvent::SERVICE_ALIAS, $pKey, $pAlias, null));
        }
    }
    
    /**
     * @see ContainerInterface::addTag()
     */
    public function addTag($pKey, $pTag)
    {
        if($this->has($pKey))
        {
            if(isset($this->_aliases[$pKey]))
            {
                $pKey = $this->_aliases[$pKey];
            }
            
            if(!in_array($pTag, $this->_data[$pKey]['tags']))
            {
                $this->_data[$pKey]['tags'][] = $pTag;
            }
        }
    }

    /**
     * @see ContainerInterface::findByTag()
     */
    public function findByTag($pTag, array $pArguments = [], $pDefault = null)
    {
        $keys = [];
        
        foreach($this->_data as $key => $value)
        {
            if(in_array($pTag, $value['tags']))
            {
                $keys[] = $key;
            }
        }
        
        $result = [];
        
        foreach($keys as $key)
        {
            $s = $this->get($key, ['arguments' => $pArguments, 'resolve' => false], null);
            
            if(null !== $s)
            {
                $result[$key] = $s;
            }
        }
        
        if(count($result) == 0)
        {
            if(count($this->_providers) > 0)
            {
                foreach($this->_providers as $providers)
                {
                    $providers->register($this);
                }

                return $this->findByTag($pTag, $pArguments, $pDefault);
            }
            
            return is_callable($pDefault) ? $pDefault() : $pDefault;
        }
        
        return $result;
    }
    
    /**
     * @see ContainerInterface::raw()
     * @throws \InvalidArgumentException
     */
    public function raw($pKey)
    {
        if(!$this->has($pKey))
        {
            throw new \InvalidArgumentException(sprintf('Service "%s" is not defined.', $pKey));
        }
        
        if(isset($this->_aliases[$pKey]))
        {
            $pKey = $this->_aliases[$pKey];
        }
        
        $data = $this->_data[$pKey];
        $data['aliases'] = [];
            
        foreach($this->_aliases as $key => $value)
        {
            if($value == $pKey)
            {
                $data['aliases'][] = $key;
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
    public function bind($pKey, $pValue, array $pOptions = [])
    {
        switch($this->_lockMode)
        {
            case self::READ_ONLY:
                throw new \LogicException('The dependency injection container is read only.');
            break;
            case self::IGNORE_IF_ALREADY_EXISTS:
            case self::THROW_IF_ALREADY_EXISTS:
                if($this->has($pKey))
                {
                    if($this->_lockMode == self::THROW_IF_ALREADY_EXISTS)
                    {
                        throw new \LogicException(sprintf('Service "%s" has already been defined.', $pKey));
                    }
                    
                    return;
                }
            break;
        }
        
        $this->_data[$pKey] = [
            'type' => self::BIND, 
            'value' => $pValue,
            'tags' => isset($pOptions['tags']) ?(array)$pOptions['tags'] : []
        ];
        
        if(isset($pOptions['alias']))
        {
            foreach((array)$pOptions['alias'] as $alias)
            {
                $this->addAlias($pKey, $alias);
            }
        }
        
        $this->dispatch(new ContainerEvent(ContainerEvent::SERVICE_CREATED, $pKey, null, self::BIND));
    }

    /**
     * @param string $pKey
     * @param mixed $pValue
     * @param array $pOptions
     * @param mixed $pAliases
     * @throws \LogicException
     */
    public function singleton($pKey, $pValue, array $pOptions = [])
    {
        switch($this->_lockMode)
        {
            case self::READ_ONLY:
                throw new \LogicException('The dependency injection container is read only.');
            break;
            case self::IGNORE_IF_ALREADY_EXISTS:
            case self::THROW_IF_ALREADY_EXISTS:
                if($this->has($pKey))
                {
                    if($this->_lockMode == self::THROW_IF_ALREADY_EXISTS)
                    {
                        throw new \LogicException(sprintf('Service "%s" has already been defined.', $pKey));
                    }
                    
                    return;
                }
            break;
        }
        
        $value = function(self $pContainer) use ($pValue)
        {
            static $instance;
            
            if(null === $instance)
            {
                $instance = is_callable($pValue) ? $pValue($pContainer) : $pValue;
            }
            
            return $instance;
        };
        
        $this->_data[$pKey] = [
            'type' => self::SINGLETON, 
            'value' => $value,
            'tags' => isset($pOptions['tags']) ?(array)$pOptions['tags'] : []
        ];
        
        if(isset($pOptions['alias']))
        {
            foreach((array)$pOptions['alias'] as $alias)
            {
                $this->addAlias($pKey, $alias);
            }
        }
        
        $this->dispatch(new ContainerEvent(ContainerEvent::SERVICE_CREATED, $pKey, null, self::SINGLETON));
    }
    
    /**
     * @param string $pKey
     * @param callable $pValue
     * @param array $pOptions
     * @throws \LogicException
     */
    public function wrap($pKey, callable $pValue, array $pOptions = [])
    {
        switch($this->_lockMode)
        {
            case self::READ_ONLY:
                throw new \LogicException('The dependency injection container is read only.');
            break;
            case self::IGNORE_IF_ALREADY_EXISTS:
            case self::THROW_IF_ALREADY_EXISTS:
                if($this->has($pKey))
                {
                    if($this->_lockMode == self::THROW_IF_ALREADY_EXISTS)
                    {
                        throw new \LogicException(sprintf('Service "%s" has already been defined.', $pKey));
                    }
                    
                    return;
                }
            break;
        }
        
        $value = function() use ($pValue)
        {
            return $pValue;
        };
        
        $this->_data[$pKey] = [
            'type' => self::WRAP, 
            'value' => $value,
            'tags' => isset($pOptions['tags']) ?(array)$pOptions['tags'] : []
        ];
        
        if(isset($pOptions['alias']))
        {
            foreach((array)$pOptions['alias'] as $alias)
            {
                $this->addAlias($pKey, $alias);
            }
        }
        
        $this->dispatch(new ContainerEvent(ContainerEvent::SERVICE_CREATED, $pKey, null, self::WRAP));
    }
    
    /**
     * @param string $pKey
     * @param callable $pValue
     */
    public function extend($pKey, callable $pValue)
    {
        if(!$this->has($pKey))
        {
            throw new \InvalidArgumentException(sprintf('Service "%s" is not defined.', $pKey));
        }
        
        if(isset($this->_aliases[$pKey]))
        {
            $pKey = $this->_aliases[$pKey];
        }

        $value = $this->_data[$pKey]['value'];
        
        $service = function() use($value)
        {
            return is_callable($value) ? $value($this) : $value;
        };

        $type = $this->_data[$pKey]['type'];

        $this->_data[$pKey]['value'] = function(self $pContainer) use ($service, $pValue, $type) 
        {
            if($type == self::SINGLETON)
            {
                static $instance;

                if(null === $instance)
                {
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
    public function addProvider(ProviderInterface $pProvider)
    {
        if(!$pProvider->isDeferred())
        {
            $pProvider->register($this);
        }
        else
        {
            $this->_providers[] = $pProvider;
        }
    }
    
    /**
     * @return array
     */
    public function getProviders()
    {
        return $this->_providers;
    }

    /**
     * @see ContainerInterface::merge()
     * @throws \LogicException
     */
    public function merge($pData)
    {
        if($pData instanceof self)
        {
            $gets = $pData->gets(['raw' => true, 'providers' => true]);
            $pData = $gets['data'];
        }
        
        $aliases = [];
        
        foreach($pData as $key => &$value)
        {
            switch($this->_lockMode)
            {
                case self::READ_ONLY:
                    throw new \LogicException('The dependency injection container is read only.');
                break;
                case self::IGNORE_IF_ALREADY_EXISTS:
                case self::THROW_IF_ALREADY_EXISTS:
                    if($this->has($pKey))
                    {
                        if($this->_lockMode == self::THROW_IF_ALREADY_EXISTS)
                        {
                            throw new \LogicException(sprintf('Service "%s" has already been defined.', $key));
                        }
                        
                        unset($pData[$key]);
                        continue 2;
                    }
                break;
            }
            
            if(isset($value['aliases']))
            {
                foreach($value['aliases'] as $alias)
                {
                    $aliases[$alias] = $key;
                }
                
                unset($value['aliases']);
            }
        }
        
        $this->_data = array_merge($this->_data, $pData);
        
        if(isset($gets['providers']))
        {
            foreach($gets['providers'] as $provider)
            {
                $this->addProvider($provider);
            }
        }
        
        foreach($aliases as $key => $value)
        {
            $this->addAlias($value, $key);
        }
    }

    /**
     * @ignore
     */
    public function __debugInfo()
    {
        $data = $this->gets(['raw' => true, 'providers' => true]);
        $provides = [];
        
        foreach ($data['data'] as $key => &$config)
        {
            unset($config['value']);
        }
        
        foreach($data['providers'] as $provider)
        {
            $provides = array_merge($provides, $provider->provides());
        }
        
        return [
            'data' => $data['data'],
            'aliases' => $this->_aliases,
            'provides' => $provides,
        ];
    }
}
