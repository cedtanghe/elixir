<?php

namespace Elixir\DI;

use Elixir\Dispatcher\Dispatcher;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Container extends Dispatcher implements ContainerInterface
{
    /**
     * @var array 
     */
    protected $_data = array();
    
    /**
     * @var array 
     */
    protected $_aliases = array();
    
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
        
        return isset($this->_data[$pKey]);
    }
    
    /**
     * @see ContainerInterface::get()
     * @throws \LogicException
     */
    public function get($pKey, array $pArguments = null, $pDefault = null)
    {
        if($this->has($pKey))
        {
            if(isset($this->_aliases[$pKey]))
            {
                $pKey = $this->_aliases[$pKey];
            }
            
            $data = $this->_data[$pKey]['value'];
            $arguments = array($this);
            
            if(null !== $pArguments)
            {
                if($this->_data[$pKey]['type'] != self::SIMPLE)
                {
                    throw new \LogicException(sprintf('"%s" service must be "simple" type.', $root));
                }
                
                $arguments[] = $pArguments;
            }
            
            if(is_callable($data))
            {
                return call_user_func_array($data, $arguments);
            }

            return $data;
        }
        
        return is_callable($pDefault) ? $pDefault() : $pDefault;
    }

    /**
     * @see ContainerInterface::set()
     * @throws \LogicException
     */
    public function set($pKey, $pValue, array $pOptions = array())
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
                        throw new \LogicException(sprintf('"%s" service has already been defined.', $pKey));
                    }
                    
                    return;
                }
            break;
        }
        
        if(!isset($pOptions['type']))
        {
            $pOptions['type'] = self::SIMPLE;
        }
        
        $type = $pOptions['type'];
        $tags = (array)(isset($pOptions['tags']) ? $pOptions['tags'] : array());
        $aliases = (array)(isset($pOptions['aliases']) ? $pOptions['aliases'] : array());
        
        switch($type)
        {
            case self::SINGLETON:
                $this->singleton($pKey, $pValue, $tags, $aliases);
            break;
            case self::PROTECT:
                $this->protect($pKey, $pValue, $tags, $aliases);
            break;
            default:
                $this->_data[$pKey] = array(
                    'type' => self::SIMPLE, 
                    'value' => $pValue,
                    'tags' => $tags
                );
                
                $this->dispatch(new ContainerEvent(ContainerEvent::SERVICE_CREATED, $pKey, null, self::SIMPLE));
                
                foreach($aliases as $alias)
                {
                    $this->addAlias($pKey, $alias);
                }
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
     * @see ContainerInterface::gets()
     */
    public function gets(array $pOptions = array())
    {
        $data = array();
        $raw = isset($pOptions['raw']) && $pOptions['raw'];
        $withConfiguration = isset($pOptions['withConfiguration']) && $pOptions['withConfiguration'];
        
        foreach($this->_data as $key => $value)
        {
            $data[$key] = $raw ? $this->raw($key, $withConfiguration) : $this->get($key);
        }

        return $data;
    }
    
    /**
     * @see ContainerInterface::sets()
     */
    public function sets(array $pData, array $pOptions = array())
    {
        $this->_data = array();
        
        foreach($pData as $key => $value)
        {
            $this->set($key, $value, $pOptions);
        }
    }
    
    /**
     * @see ContainerInterface::hasAlias()
     */
    public function hasAlias($pAlias, $pKey = null)
    {
        if(isset($this->_aliases[$pAlias]))
        {
            return null !== $pKey ? $this->_aliases[$pAlias] == $pKey : true;
        }
        
        return false;
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
     * @see ContainerInterface::hasTag()
     */
    public function hasTag($pKey, $pTag)
    {
        if($this->has($pKey))
        {
            if(isset($this->_aliases[$pKey]))
            {
                $pKey = $this->_aliases[$pKey];
            }
            
            return in_array($pTag, $this->_data[$pKey]['tags']);
        }
        
        return false;
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
     * @param string $pTag 
     * @param mixed $pDefault
     * @param array $pOptions
     * @return array|mixed 
     */
    public function getValuesByTag($pTag, $pDefault = null, array $pOptions = array())
    {
        $keys = array();
        
        foreach($this->_data as $key => $value)
        {
            if(in_array($pTag, $value['tags']))
            {
                $keys[] = $key;
            }
        }
        
        $raw = isset($pOptions['raw']) && $pOptions['raw'];
        $withConfiguration = isset($pOptions['withConfiguration']) && $pOptions['withConfiguration'];

        $result = array();
        
        foreach($keys as $value)
        {
            $result[$value] = $raw ? $this->raw($value, $withConfiguration) : $this->get($value);
        }
        
        if(count($result) == 0)
        {
            return is_callable($pDefault) ? $pDefault() : $pDefault;
        }
        
        return $result;
    }
    
    /**
     * @see ContainerInterface::raw()
     * @throws \InvalidArgumentException
     */
    public function raw($pKey, $pWithConfiguration = false)
    {
        if(!$this->has($pKey))
        {
            throw new \InvalidArgumentException(sprintf('Key "%s" is not defined.', $pKey));
        }
        
        if(isset($this->_aliases[$pKey]))
        {
            $pKey = $this->_aliases[$pKey];
        }
        
        $data = $this->_data[$pKey];
        
        if($pWithConfiguration)
        {
            $data['aliases'] = array();
            
            foreach($this->_aliases as $key => $value)
            {
                if($value == $pKey)
                {
                    $data['aliases'][] = $key;
                }
            }
            
            return $data;
        }
        
        return $data['value'];
    }
    
    /**
     * @param string $pKey
     * @param mixed $pValue
     * @param mixed $pTags
     * @param mixed $pAliases
     * @throws \LogicException
     */
    public function singleton($pKey, $pValue, $pTags = array(), $pAliases = array())
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
                        throw new \LogicException(sprintf('"%s" service has already been defined.', $pKey));
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
        
        $this->_data[$pKey] = array(
            'type' => self::SINGLETON, 
            'value' => $value,
            'tags' => (array)$pTags
        );
        
        $this->dispatch(new ContainerEvent(ContainerEvent::SERVICE_CREATED, $pKey, null, self::SINGLETON));
        
        foreach((array)$pAliases as $alias)
        {
            $this->addAlias($pKey, $alias);
        }
    }
    
    /**
     * @param string $pKey
     * @param callable $pValue
     * @param mixed $pTags
     * @param mixed $pAliases
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function protect($pKey, $pValue, $pTags = array(), $pAliases = array())
    {
        if(!is_callable($pValue))
        {
            throw new \InvalidArgumentException('Value argument must be a callable.');
        }
        
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
                        throw new \LogicException(sprintf('"%s" service has already been defined.', $pKey));
                    }
                    
                    return;
                }
            break;
        }
        
        $value = function() use ($pValue)
        {
            return $pValue;
        };
        
        $this->_data[$pKey] = array(
            'type' => self::PROTECT, 
            'value' => $value,
            'tags' => (array)$pTags
        );
        
        $this->dispatch(new ContainerEvent(ContainerEvent::SERVICE_CREATED, $pKey, null, self::PROTECT));
        
        foreach((array)$pAliases as $alias)
        {
            $this->addAlias($pKey, $alias);
        }
    }
    
    /**
     * @see ContainerInterface::extend()
     * @throws \InvalidArgumentException
     */
    public function extend($pKey, $pValue)
    {
        if(!$this->has($pKey))
        {
            throw new \InvalidArgumentException(sprintf('Key "%s" is not defined.', $pKey));
        }
        
        if(!is_callable($pValue))
        {
            throw new \InvalidArgumentException('Value argument must be a callable.');
        }
        
        if(isset($this->_aliases[$pKey]))
        {
            $pKey = $this->_aliases[$pKey];
        }

        $me = $this;
        $value = $this->_data[$pKey]['value'];

        $service = function() use($me, $value)
        {
            return is_callable($value) ? $value($me) : $value;
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
     * @param string $pKey
     * @return string
     */
    public function getStorageType($pKey)
    {
        $data = $this->raw($pKey, true);
        return $data['type'];
    }

        /**
     * @see ContainerInterface::load()
     */
    public function load(ProviderInterface $pProvider)
    {
        $pProvider->load($this);
    }
    
    /**
     * @see ContainerInterface::merge()
     * @throws \LogicException
     */
    public function merge($pData)
    {
        if($pData instanceof self)
        {
            $pData = $pData->gets(array('raw' => true, 'withConfiguration' => true));
        }
        
        $aliases = array();
        
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
                            throw new \LogicException(sprintf('"%s" service has already been defined.', $key));
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
        
        foreach($aliases as $key => $value)
        {
            $this->addAlias($value, $key);
        }
    }
}