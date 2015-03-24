<?php

namespace Elixir\DB\ObjectMapper;

use Elixir\DB\ObjectMapper\Collection;
use Elixir\DB\ObjectMapper\EntityEvent;
use Elixir\DB\ObjectMapper\Relation\RelationInterface;
use Elixir\Dispatcher\Dispatcher;
use Elixir\Util\Str;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
abstract class EntityAbstract extends Dispatcher implements EntityInterface
{
    /**
     * @var array
     */
    protected static $mutatorsGet = [];

    /**
     * @var array
     */
    protected static $mutatorsSet = [];
    
    /**
     * @var string
     */
    protected $className;
    
    /**
     * @var mixed
     */
    protected $ignoreValue = self::IGNORE_VALUE;

    /**
     * @var array
     */
    protected $fillable = [];
    
    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var array
     */
    protected $related = [];
    
    /**
     * @var array
     */
    protected $original = [];

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var string
     */
    protected $state = self::FILLABLE;
    
    /**
     * @param array $data
     */
    public function __construct(array $data = null)
    {
        $this->className = get_class($this);
        
        $this->state = self::FILLABLE;
        $this->defineFillable();
        $this->dispatch(new EntityEvent(EntityEvent::DEFINE_FILLABLE));
        $this->state = self::GUARDED;
        $this->defineGuarded();
        $this->dispatch(new EntityEvent(EntityEvent::DEFINE_GUARDED));

        if (!empty($data)) 
        {
            $this->hydrate($data, ['raw' => true, 'sync' => true]);
        }
    }
    
    /**
     * Declares columns
     */
    abstract protected function defineFillable();
    
    /**
     * Declares relations and others
     */
    protected function defineGuarded(){}
    
    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }
    
    /**
     * @see EntityInterface::setIgnoreValue()
     */
    public function setIgnoreValue($value)
    {
        $this->ignoreValue = $value;
    }

    /**
     * @see EntityInterface::getIgnoreValue()
     */
    public function getIgnoreValue()
    {
        return $this->ignoreValue;
    }
    
    /**
     * @param string $value
     */
    public function setState($value) 
    {
        $this->state = $value;
    }

    /**
     * @return string
     */
    public function getState() 
    {
        return $this->state;
    }
    
    /**
     * @return array
     */
    public function getFillableKeys() 
    {
        return $this->fillable;
    }

    /**
     * @return array
     */
    public function getGuardedKeys()
    {
        return $this->guarded;
    }

    /**
     * @return array
     */
    public function getRelatedKeys()
    {
        return array_keys($this->related);
    }

    /**
     * @param string $key
     * @return string
     */
    public function getRelatedType($key) 
    {
        return isset($this->related[$key]) ? $this->related[$key] : null;
    }

    /**
     * @return boolean
     */
    public function isReadOnly() 
    {
        return $this->state == self::READ_ONLY;
    }

    /**
     * @return boolean
     */
    public function isFillable() 
    {
        return $this->state == self::FILLABLE;
    }

    /**
     * @return boolean
     */
    public function isGuarded()
    {
        return $this->state == self::GUARDED;
    }
    
    /**
     * @see EntityInterface::isModified()
     */
    public function isModified($state = self::SYNC_ALL) 
    {
        $count = $this->getModified($state);
        return count($count) > 0;
    }

    /**
     * @see EntityInterface::getModified()
     */
    public function getModified($state = self::SYNC_ALL) 
    {
        $modified = [];
        $providers = [];
        
        switch($state)
        {
            case self::SYNC_FILLABLE:
                $providers = [$this->fillable];
                break;
            case self::SYNC_GUARDED:
                $providers = [$this->guarded];
                break;
            case self::SYNC_ALL:
                $providers = [$this->fillable, $this->guarded];
                break;
        }

        foreach ($providers as $provider)
        {
            foreach ($provider as $f) 
            {
                $value = $this->get($f);
                
                if (!array_key_exists($f, $this->original) || $this->original[$f] !== $value) 
                {
                    $modified[$f] = $value;
                }
            }
        }

        return $modified;
    }
    
    /**
     * @see EntityInterface::sync()
     */
    public function sync($state = self::SYNC_ALL) 
    {
        $providers = [];
        
        switch($state)
        {
            case self::SYNC_FILLABLE:
                $providers = [$this->fillable];
                break;
            case self::SYNC_GUARDED:
                $providers = [$this->guarded];
                break;
            case self::SYNC_ALL:
                $providers = [$this->fillable, $this->guarded];
                break;
        }
        
        foreach ($providers as $provider)
        {
            foreach ($provider as $f) 
            {
                $this->original[$f] = $this->get($f);
            }
        }
    }

    /**
     * @see EntityInterface::has()
     */
    public function has($key) 
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * @see EntityInterface::set()
     * @throws \InvalidArgumentException
     */
    public function set($key, $value) 
    {
        if ($this->isReadOnly()) 
        {
            if (!$this->has($key))
            {
                throw new \InvalidArgumentException(sprintf('Key "%s" is a not declared property.', $key));
            }
        }

        if (is_array($value)) 
        {
            $value = new Collection($value, false);
        }
        else if ($value instanceof \ArrayObject)
        {
            $value = new Collection($value->getArrayCopy(), false);
        }
        
        if ($this->isFillable()) 
        {
            if (!in_array($key, $this->fillable))
            {
                $this->fillable[] = $key;
            }
        } 
        else if ($this->isGuarded()) 
        {
            if (!in_array($key, $this->guarded)) 
            {
                $this->guarded[] = $key;
            }

            if (array_key_exists($key, $this->related))
            {
                $relation = $this->get($key);
                $relation->setRelated($value, $value !== $this->ignoreValue);

                $value = $relation;
            } 
            else if ($value instanceof RelationInterface) 
            {
                $this->related[$key] = $value->getType();
            }
        }

        $this->data[$key] = $value;;
    }

    /**
     * @see EntityInterface::get()
     */
    public function get($key, $default = null) 
    {
        if (!$this->has($key)) 
        {
            return is_callable($default) ? call_user_func($default) : $default;
        }

        return $this->data[$key];
    }
    
    /**
     * @see EntityInterface::hydrate()
     */
    public function hydrate(array $data, array $options = [])
    {
        $options = array_merge(
            [
                'raw' => true,
                'sync' => true
            ],
            $options
        );
        
        $references = [];
        $entities = [];

        foreach ($data as $key => $value) 
        {
            if (false !== strpos($key, self::ENTITY_SEPARATOR)) 
            {
                $reference = null;
                $segments = explode(self::ENTITY_SEPARATOR, $key);

                if (count($segments) == 3) 
                {
                    $reference = array_shift($segments);
                    $class = $segments[0];
                } 
                else 
                {
                    if ($segments[0] != $this->className) 
                    {
                        $class = $segments[0];

                        if (!isset($references[$class])) 
                        {
                            $reference = lcfirst(pathinfo($class, PATHINFO_BASENAME));
                            $references[$class] = $reference;
                        } 
                        else 
                        {
                            $reference = $references[$class];
                        }
                    }
                }

                if (null !== $reference) 
                {
                    if (!isset($entities[$reference])) 
                    {
                        $entities[$reference] = ['class' => $class, 'value' => []];
                    }

                    $entities[$reference]['value'][$segments[1]] = $value;
                    continue;
                }
            }

            if ($value instanceof Collection || is_array($value))
            {
                $value = $this->hydrateCollection($value, $options);
            }
            
            if ($options['raw']) 
            {
                $this->set($key, $value);
            } 
            else 
            {
                $this->$key = $value;
            }
        }

        if (count($entities) > 0) 
        {
            foreach ($entities as $key => $value) 
            {
                $event = new EntityEvent(EntityEvent::CREATE_ENTITY, ['entity' => $value['class']]);
                $this->dispatch($event);
                
                $entity = $event->getEntity();
                
                if(!$entity instanceof EntityInterface)
                {
                    $entity = new $entity();
                }
                
                $entity->hydrate($value['value'], $options);

                if ($options['raw']) 
                {
                    $this->set($key, $entity);
                } 
                else
                {
                    $this->$key = $entity;
                }
            }
        }

        if ($options['sync'])
        {
            $this->sync();
        }
    }
    
    /**
     * @param array|Collection $data
     * @param array $options
     * @return mixed
     */
    protected function hydrateCollection($data, array $options)
    {
        if (isset($data['_class']))
        {   
            $event = new EntityEvent(EntityEvent::CREATE_ENTITY, ['entity' => $data['_class']]);
            $this->dispatch($event);

            $entity = $event->getEntity();

            if(!$entity instanceof EntityInterface)
            {
                $entity = new $entity();
            }
            
            $entity->hydrate($data, $options);
            $data = $entity;
        } 
        else
        {
            foreach ($data as $key => &$value)
            {
                if ($value instanceof Collection || is_array($value))
                {
                    $value = $this->hydrateCollection($value, $options);
                }
            }
        }

        return $data;
    }

    /**
     * @see EntityInterface::export()
     */
    public function export(array $members = [], array $omitMembers = [], array $options = [])
    {
        $options = array_merge(
            [
                'raw' => true,
                'format' => self::FORMAT_PHP
            ],
            $options
        );
        
        $data = [];

        foreach (array_keys($this->data) as $value)
        {
            if (in_array($value, $omitMembers) || (count($members) > 0 && !in_array($value, $members)))
            {
                continue;
            } 
            else 
            {
                $v = $options['raw'] ? $this->get($value) : $this->$value;

                if (array_key_exists($value, $this->related))
                {
                    if ($v instanceof RelationInterface) 
                    {
                        $v = $v->getRelated();
                    }
                }

                if ($v instanceof EntityInterface) 
                {
                    $v = $v->export([], [], $options);
                } 
                else if ($v instanceof Collection)
                {
                    $v = $this->exportCollection($v, $options);
                }

                $data[$value] = $v;
                $data['_class'] = $this->className;
            }
        }
        
        if($options['format'] == self::FORMAT_JSON)
        {
            $data = json_encode($data);
        }
        
        return $data;
    }

    /**
     * @param Collection $data
     * @param array $options
     * @return array
     */
    protected function exportCollection(Collection $data, $options) 
    {
        foreach ($data as $key => &$value) 
        {
            if ($value instanceof Collection) 
            {
                $value = $this->exportCollection($value, $options);
            } 
            else if ($value instanceof EntityInterface)
            {
                $value = $value->export([], [], $options);
            }
        }

        return $data;
    }

    /**
     * @ignore
     */
    public function __isset($key) 
    {
        return $this->has($key);
    }

    /**
     * @ignore
     */
    public function &__get($key) 
    {
        if (isset(static::$mutatorsGet[$this->className][$key]))
        {
            if (false !== static::$mutatorsGet[$this->className][$key]) 
            {
                return $this->{static::$mutatorsGet[$this->className][$key]}();
            }
        } 
        else 
        {
            $method = 'get' . Str::camelize($key);

            if (method_exists($this, $method)) 
            {
                static::$mutatorsGet[$this->className][$key] = $method;
                return $this->{$method}();
            } 
            else
            {
                static::$mutatorsGet[$this->className][$key] = false;
            }
        }

        $value = $this->get($key);

        // Property is a relationship
        if (array_key_exists($key, $this->related))
        {
            if (!$value->isFilled()) 
            {
                $value->load();
            }

            $value = $value->getRelated();
        }

        return $value;
    }

    /**
     * @ignore
     */
    public function __set($key, $value) 
    {
        if (isset(static::$mutatorsSet[$this->className][$key])) 
        {
            if (false !== static::$mutatorsSet[$this->className][$key]) 
            {
                $this->{static::$mutatorsSet[$this->className][$key]}($value);
                return;
            }
        } 
        else 
        {
            $method = 'set' . Str::camelize($key);

            if (method_exists($this, $method)) 
            {
                static::$mutatorsSet[$this->className][$key] = $method;
                $this->{$method}($value);

                return;
            } 
            else
            {
                static::$mutatorsSet[$this->className][$key] = false;
            }
        }

        $this->set($key, $value);
    }

    /**
     * @ignore
     */
    public function __unset($key) 
    {
        $this->set($key, $this->ignoreValue);
    }

    /**
     * @ignore
     */
    public function __toString() 
    {
        return $this->export([], [], ['format' => self::FORMAT_JSON]);
    }
    
    /**
     * @ignore
     */
    public function __debugInfo()
    {
        return [
            'name' => $this->name,
            'data' => $this->export()
        ];
    }
}
