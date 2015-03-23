<?php

namespace Elixir\DB\ObjectMapper;

use Elixir\DB\ObjectMapper\CollectionEvent;
use Elixir\Dispatcher\Dispatcher;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class Collection extends Dispatcher implements \Iterator, \Countable 
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var boolean
     */
    protected $useEvents;

    /**
     * @param array $data
     * @param boolean $useEvents
     */
    public function __construct(array $data = [], $useEvents = false) 
    {
        $this->data = $data;
        $this->setUseEvents($useEvents);
    }

    /**
     * @param boolean $value
     */
    public function setUseEvents($value) 
    {
        $this->useEvents = $value;
    }

    /**
     * @return boolean
     */
    public function isUseEvents() 
    {
        return $this->useEvents;
    }

    /**
     * @param mixed $value
     */
    public function append($value)
    {
        array_push($this->data, $value);
        
        if($this->useEvents)
        {
            $this->dispatch(new CollectionEvent(CollectionEvent::VALUE_ADDED, ['object' => $value]));
        }
    }

    /**
     * @param mixed $value
     */
    public function prepend($value) 
    {
        array_unshift($this->data, $value);
        
        if($this->useEvents)
        {
            $this->dispatch(new CollectionEvent(CollectionEvent::VALUE_ADDED, ['object' => $value]));
        }
    }

    /**
     * @param mixed $value
     */
    public function remove($value) 
    {
        $pos = $this->search($value);

        if (false !== $pos) 
        {
            $this->splice($pos, 1);
            
            if($this->useEvents)
            {
                $this->dispatch(new CollectionEvent(CollectionEvent::VALUE_REMOVED, ['object' => $value]));
            }
        }
    }
    
    /**
     * @param mixed $needle
     * @return boolean
     */
    public function in($needle) 
    {
        return in_array($needle, $this->data, true);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function search($value)
    {
        return array_search($value, $this->data);
    }

    /**
     * @param integer $offset
     * @param integer $length
     * @param array $replacement
     * @return array
     */
    public function splice($offset, $length, $replacement = [])
    {
        $values = array_splice($this->data, $offset, $length, $replacement);
        
        if ($this->useEvents)
        {
            foreach ($values as $value)
            {
                $this->dispatch(new CollectionEvent(CollectionEvent::VALUE_REMOVED, ['object' => $value]));
            }
        }
        
        return $values;
    }
    
    /**
     * @param integer $offset
     * @param integer $length
     * @param boolean $preserveKeys
     * @return array
     */
    public function slice($offset, $length, $preserveKeys = false)
    {
        return array_slice($this->data, $offset, $length, $preserveKeys);
    }
    
    public function shuffle() 
    {
        shuffle($this->data);
    }
    
    public function reverse() 
    {
        array_reverse($this->data);
    }
    
    /**
     * @return mixed
     */
    public function shift() 
    {
        $value = array_shift($this->data);
        
        if($this->useEvents)
        {
            $this->dispatch(new CollectionEvent(CollectionEvent::VALUE_REMOVED, ['object' => $value]));
        }
        
        return $value;
    }

    /**
     * @return mixed
     */
    public function pop()
    {
        $value = array_pop($this->data);
        
        if($this->useEvents)
        {
            $this->dispatch(new CollectionEvent(CollectionEvent::VALUE_REMOVED, ['object' => $value]));
        }
        
        return $value;
    }
    
    /**
     * @param string $method
     * @param array $arguments
     * @return boolean
     */
    public function sort($method = 'sort', $arguments = []) 
    {
        return call_user_func_array($method, array_merge($this->data, $arguments));
    }
    
    /**
     * @ignore
     */
    public function rewind() 
    {
        return reset($this->data);
    }

    /**
     * @ignore
     */
    public function current() 
    {
        return current($this->data);
    }

    /**
     * @ignore
     */
    public function key()
    {
        return key($this->data);
    }

    /**
     * @ignore
     */
    public function next() 
    {
        return next($this->data);
    }

    /**
     * @ignore
     */
    public function valid()
    {
        return null !== key($this->data);
    }

    /**
     * @ignore
     */
    public function count() 
    {
        return count($this->data);
    }
    
    /**
     * @return array
     */
    public function getArrayCopy() 
    {
        return array_slice($this->data, 0);
    }
    
    /**
     * @param array|Collection $data
     */
    public function merge($data) 
    {
        foreach ($data as $value)
        {
            $this->append($value);
        }
    }
}
