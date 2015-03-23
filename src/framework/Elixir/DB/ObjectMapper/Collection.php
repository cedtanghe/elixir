<?php

namespace Elixir\DB\ObjectMapper;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class Collection extends \ArrayObject 
{
    /**
     * @param mixed $value
     * @return boolean
     */
    public static function isCollection($value)
    {
        return $value instanceof \ArrayObject;
    }

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
        parent::__construct($data, \ArrayObject::STD_PROP_LIST);
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
    public function prepend($value) 
    {
        $data = $this->getArrayCopy();
        array_unshift($data, $value);

        $this->exchangeArray($data);
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
        }
    }

    /**
     * @param mixed $needle
     * @return boolean
     */
    public function in($needle) 
    {
        return in_array($needle, $this->getArrayCopy(), true);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function search($value)
    {
        return array_search($value, $this->getArrayCopy());
    }

    /**
     * @param integer $offset
     * @param integer $length
     * @param array $replacement
     */
    public function splice($offset, $length, $replacement = [])
    {
        $data = $this->getArrayCopy();
        array_splice($data, $offset, $length, $replacement);

        $this->exchangeArray($data);
    }
    
    /**
     * @return boolean
     */
    public function shuffle() 
    {
        $data = $this->getArrayCopy();
        $result = shuffle($data);

        if ($result)
        {
            $this->exchangeArray($data);
        }
        
        return $result;
    }

    public function reverse()
    {
        $this->exchangeArray(array_reverse($this->getArrayCopy()));
    }

    /**
     * @return mixed
     */
    public function shift() 
    {
        $data = $this->getArrayCopy();
        $value = array_shift($data);

        $this->exchangeArray($data);
        return $value;
    }

    /**
     * @return mixed
     */
    public function pop()
    {
        $data = $this->getArrayCopy();
        $value = array_pop($data);

        $this->exchangeArray($data);
        return $value;
    }
    
    /**
     * @param array|\ArrayObject $data
     * @throws \InvalidArgumentException
     */
    public function merge($data)
    {
        if (static::isCollection($data))
        {
            $data = $data->getArrayCopy();
        } 
        else if (!is_array($data))
        {
            throw new \InvalidArgumentException('Data must be of type array or \ArrayObject.');
        }

        $this->exchangeArray(array_merge($this->getArrayCopy(), $data));
    }

    /**
     * @return array
     */
    public function export() 
    {
        $data = [];

        foreach ($this->getArrayCopy() as $key => $value)
        {
            if ($value instanceof self)
            {
                $data[$key] = $value->export();
            } 
            else if (is_array($value))
            {
                $data[$key] = static::create($value)->export();
            } 
            else if (is_object($value)) 
            {
                if (method_exists($value, 'export')) 
                {
                    $data[$key] = $value->export();
                } 
                else 
                {
                    $data[$key] = static::create(get_object_vars($value))->export();
                }
            } 
            else
            {
                $data[$key] = $value;
            }
        }

        return $data;
    }
}
