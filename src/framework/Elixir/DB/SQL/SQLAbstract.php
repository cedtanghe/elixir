<?php

namespace Elixir\DB\SQL;

use Elixir\DB\SQL\Expr;
use Elixir\DB\SQL\SQLInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

abstract class SQLAbstract implements SQLInterface
{
    /**
     * @param mixed $pParameter
     * @return mixed
     */
    public static function protect($pParameter)
    {
        if($pParameter instanceof Expr)
        {
            $pParameter = $pParameter->getExpr();
            
            if(null === $pParameter)
            {
                return 'NULL';
            }
            
            return $pParameter;
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
     * @var callable
     */
    protected $_quoteMethod = '\Elixir\DB\SQL\SQLAbstract::protect';
    
    /**
     * @var array
     */
    protected $_bindValues = [];
    
    /**
     * @see SQLInterface::setQuoteMethod()
     */
    public function setQuoteMethod(callable $pValue)
    {
        $this->_quoteMethod = $pValue;
    }
    
    /**
     * @see SQLInterface::getQuoteMethod()
     */
    public function getQuoteMethod()
    {
        return $this->_quoteMethod;
    }
    
    /**
     * @see SQLInterface::quote()
     */
    public function quote($pParameter)
    {
        return call_user_func_array($this->_quoteMethod, [$pParameter]);
    }
    
    /**
     * @see SQLInterface::bindValue()
     */
    public function bindValue($pKey, $pValue)
    {
        $this->_bindValues[$pKey] = $pValue;
    }
    
    /**
     * @see SQLInterface::getBindValues()
     */
    public function getBindValues()
    {
        return $this->_bindValues;
    }
    
    /**
     * @see SQLInterface::assemble()
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
                    $pValues = [$pValues];
                }
            }
            
            $keys = [];
            $values = [];
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
     * @see SQLInterface::getQuery()
     */
    public function getQuery() 
    {
        return $this->render();
    }
    
    /**
     * @see SQLAbstract::getQuery()
     */
    public function __toString() 
    {
        return $this->getQuery();
    }
}
