<?php

namespace Elixir\DB\SQL;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

abstract class SQLAbstract
{
    /**
     * @param mixed $pParameter
     * @return mixed
     */
    public static function protect($pParameter)
    {
        if($pParameter instanceof Expr)
        {
            return $pParameter->getExpr();
        }
        
        if(is_array($pParameter))
        {
            foreach($pParameter as &$value)
            {
                $value = static::protect($value);
            }
            
            return implode(', ', $pParameter);
        }
        
        if (is_int($pParameter))
        {
            return $pParameter;
        } 
        
        if(is_float($pParameter)) 
        {
            return sprintf('%F', $pParameter);
        }
        
        if(null === $pParameter)
        {
            return 'NULL';
        }
        
        return '\'' . addcslashes($pParameter, "\000\n\r\\'\"\032") . '\'';
    }
    
    /**
     * @var string
     */
    const STAR = '*';
    
    /**
     * @var string
     */
    const QUANTIFIER_ALL = 'ALL';
    
    /**
     * @var string
     */
    const QUANTIFIER_DISTINCT = 'DISTINCT';
    
    /**
     * @var string
     */
    const COMBINE_UNION = 'UNION';
    
    /**
     * @var string
     */
    const COMBINE_UNION_ALL = 'UNION_ALL';
    
    /**
     * @var string
     */
    const COMBINE_EXPECT = 'EXPECT';
    
    /**
     * @var string
     */
    const COMBINE_INTERSECT = 'INTERSECT';
    
    /**
     * @var string
     */
    const JOIN_CROSS = 'CROSS';
    
    /**
     * @var string
     */
    const JOIN_INNER = 'INNER';
    
    /**
     * @var string
     */
    const JOIN_OUTER = 'OUTER';
    
    /**
     * @var string
     */
    const JOIN_LEFT = 'LEFT';
    
    /**
     * @var string
     */
    const JOIN_RIGHT = 'RIGHT';
    
    /**
     * @var string
     */
    const JOIN_NATURAL = 'NATURAL';
    
    /**
     * @var string
     */
    const ORDER_ASCENDING = 'ASC';
    
    /**
     * @var string
     */
    const ORDER_DESCENDING = 'DESC';
    
    /**
     * @var string
     */
    const ORDER_NONE = null;
    
    /**
     * @var callable
     */
    protected $_quoteMethod = '\Elixir\DB\SQL\SQLAbstract::protect';
    
    /**
     * @var array
     */
    protected $_bindValues = array();
    
    /**
     * @param callable $pValue
     * @throws \InvalidArgumentException
     */
    public function setQuoteMethod($pValue)
    {
        if(!is_callable($pValue))
        {
            throw new \InvalidArgumentException('The "quote" method must be a callable.');
        }
        
        $this->_quoteMethod = $pValue;
    }
    
    /**
     * @return callable
     */
    public function getQuoteMethod()
    {
        return $this->_quoteMethod;
    }
    
    /**
     * @param mixed $pParameter
     * @return mixed
     */
    public function quote($pParameter)
    {
        return call_user_func_array($this->_quoteMethod, array($pParameter));
    }
    
    /**
     * @param string $pKey
     * @param mixed $pValue
     * @return SQLAbstract
     */
    public function bindValue($pKey, $pValue)
    {
        $this->_bindValues[$pKey] = $pValue;
        return $this;
    }
    
    /**
     * @return array
     */
    public function getBindValues()
    {
        return $this->_bindValues;
    }
    
    /**
     * @param string $pSQL
     * @param mixed $pValues
     * @return string
     */
    public function assemble($pSQL, $pValues = null)
    {
        if(null !== $pValues)
        {
            $pValues = (array)$pValues;
            
            if(1 == substr_count($pSQL, '?'))
            {
                $isUniqArrayParameter = true;

                foreach($pValues as $key => $value)
                {
                    if(!is_int($key) || $value instanceof Expr)
                    {
                        $isUniqArrayParameter = false;
                        break;
                    }
                }

                if($isUniqArrayParameter)
                {
                    $pValues = array($pValues);
                }
            }
            
            $keys = array();
            $values = array();
            $limit = -1;

            foreach($pValues as $key => $value)
            {
                if (is_string($key))
                {
                    if(substr($key, 0, 1) != ':')
                    {
                        $key = ':' . $key;
                    }
                    
                    $keys[] = '/' . $key . '/';
                }
                else
                {
                    $limit = 1;
                    $keys[] = '/[?]/';
                }

                if(!$value instanceof Expr)
                {
                    $value = $this->quote($value);
                }

                $values[] = $value;
            }
            
            $query = preg_replace($keys, $values, $pSQL, $limit); 
            return $query;
        }
        
        return $pSQL;
    }
    
    /**
     * @see SQLAbstract::render()
     */
    public function getQuery() 
    {
        return $this->render();
    }

    /**
     * @return string
     */
    abstract public function render();
    
    /**
     * @see SQLAbstract::getQuery()
     */
    public function __toString() 
    {
        return $this->getQuery();
    }
}
