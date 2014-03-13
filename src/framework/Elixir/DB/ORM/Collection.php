<?php

namespace Elixir\DB\ORM;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class Collection extends \ArrayObject
{
    /**
     * @param mixed $pValue
     * @return boolean
     */
    public static function isCollection($pValue)
    {
        return $pValue instanceof \ArrayObject;
    }
    
    /**
     * @param array $pData
     * @param boolean $pAutoCreated
     * @return Collection
     */
    public static function create(array $pData = array(), $pAutoCreated = false)
    {
        return new static($pData, $pAutoCreated);
    }
    
    /**
     * @var boolean
     */
    protected $_autoCreated;
    
    /**
     * @param array $pData
     * @param boolean $pAutoCreated
     */
    public function __construct(array $pData = array(), $pAutoCreated = false) 
    {
        parent::__construct($pData, \ArrayObject::STD_PROP_LIST);
        $this->_autoCreated = $pAutoCreated;
    }
    
    /**
     * @return boolean
     */
    public function isAutoCreated()
    {
        return $this->_autoCreated;
    }
    
    /**
     * @param mixed $pValue
     */
    public function prepend($pValue)
    {
        $data = $this->getArrayCopy();
        array_unshift($data, $pValue);
        
        $this->exchangeArray($data);
    }
    
    /**
     * @param mixed $pValue
     */
    public function remove($pValue)
    {
        $pos = $this->search($pValue);
        
        if(false !== $pos)
        {
            $this->splice($pos, 1);
        }
    }

    /**
     * @param mixed $pNeedle
     * @return boolean
     */
    public function in($pNeedle)
    {
        return in_array($pNeedle, $this->getArrayCopy(), true);
    }
    
    /**
     * @param mixed $pValue
     * @return mixed
     */
    public function search($pValue)
    {
        return array_search($pValue, $this->getArrayCopy());
    }

    /**
     * @param integer $pOffset
     * @param integer $pLength
     * @param array $pReplacement
     */
    public function splice($pOffset, $pLength, $pReplacement = array())
    {
        $data = $this->getArrayCopy();
        array_splice($data, $pOffset, $pLength, $pReplacement);
        
        $this->exchangeArray($data);
    }
    
    public function shuffle()
    {
        $data = $this->getArrayCopy();
        shuffle($data);
        
        $this->exchangeArray($data);
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
     * @return array
     */
    public function export()
    {
        $data = array();
        
        foreach($this->getArrayCopy() as $key => $value)
        {
            if($value instanceof self)
            {
                $data[$key] = $value->export();
            }
            else if(is_array($value))
            {
                $data[$key] = static::create($value)->export();
            }
            else if(is_object($value))
            {
                if(method_exists($value, 'export'))
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

    /**
     * @param array|\ArrayObject $pData
     * @throws \InvalidArgumentException
     */
    public function merge($pData)
    {
        if(static::isCollection($pData))
        {
            $pData = $pData->getArrayCopy();
        }
        else if(!is_array($pData))
        {
            throw new \InvalidArgumentException('Data must be of type array or \ArrayObject.');
        }
        
        $this->exchangeArray(array_merge($this->getArrayCopy(), $pData));
    }
}