<?php

namespace Elixir\DB;

use Elixir\DB\DBEvent;
use Elixir\DB\DBInterface;
use Elixir\DB\Query\QueryBuilderInterface;
use Elixir\DB\Query\QueryBuilderTrait;
use Elixir\DB\Result\PDO as ResultSet;
use Elixir\DB\SQL\Expr;
use Elixir\DB\SQL\SQLInterface;
use Elixir\Dispatcher\DispatcherTrait;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class PDO implements DBInterface, QueryBuilderInterface
{
    use DispatcherTrait;
    use QueryBuilderTrait;
    
    /**
     * @var boolean
     */
    protected $_authorizeMultipleTransactions = true;
    
    /**
     * @var boolean
     */
    protected $_hasTransaction = false;
    
    /**
     * @var integer
     */
    protected $_countTransaction = 0;
    
    /**
     * @var \PDO 
     */
    protected $_connection;
    
    /**
     * @param string $pDNS
     * @param string $pUsername
     * @param string $pPassword
     * @param array $pOptions
     */
    public function __construct($pDNS, $pUsername = null, $pPassword = null, array $pOptions = []) 
    {
        if(!isset($pOptions[\PDO::MYSQL_ATTR_INIT_COMMAND]))
        {
            $pOptions[\PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES \'UTF8\'';
        }
        
        if(!isset($pOptions[\PDO::ATTR_PERSISTENT]))
        {
            $pOptions[\PDO::ATTR_PERSISTENT] = false;
        }
        
        if(!isset($pOptions[\PDO::ATTR_ERRMODE]))
        {
            $pOptions[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;
        }
        
        $this->_connection = new \PDO($pDNS, $pUsername, $pPassword, $pOptions);
    }
    
    public function __destruct() 
    {
        $this->_connection = null;
    }
    
    /**
     * @param boolean $pValue
     */
    public function useAuthorizeMultipleTransactions($pValue)
    {
        $this->_authorizeMultipleTransactions = $pValue;
    }
    
    /**
     * @return boolean
     */
    public function isAuthorizeMultipleTransactions()
    {
        return $this->_authorizeMultipleTransactions;
    }
    
    /**
     * @see QueryBuilderInterface::getDriver()
     */
    public function getDriver() 
    {
        return $this->_connection->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }
    
    /**
     * @see DBInterface::begin()
     */
    public function begin() 
    {
        if($this->_authorizeMultipleTransactions)
        {
            $this->_countTransaction++;
            
            if($this->_hasTransaction)
            {
                return false;
            }
        }
        
        $this->_hasTransaction = $this->_connection->beginTransaction();
        return $this->_hasTransaction;
    }
    
    /**
     * @see DBInterface::rollBack()
     */
    public function rollBack()
    {
        if($this->_authorizeMultipleTransactions)
        {
            $this->_countTransaction--;
            
            if($this->_countTransaction > 0)
            {
                return false;
            }
        }
        
        $this->_hasTransaction = !$this->_connection->rollBack();
        return !$this->_hasTransaction;
    }

    /**
     * @see DBInterface::commit()
     */
    public function commit()
    {
        if($this->_authorizeMultipleTransactions)
        {
            $this->_countTransaction--;
            
            if($this->_countTransaction > 0)
            {
                return false;
            }
        }
        
        $this->_hasTransaction = !$this->_connection->commit();
        return !$this->_hasTransaction;
    }
    
    /**
     * @see DBInterface::inTransaction()
     */
    public function inTransaction()
    {
        return $this->_hasTransaction;
    }
    
    /**
     * @see DBInterface::quote()
     */
    public function quote($pValue, $pType = null)
    {
        if($pValue instanceof Expr)
        {
            $pValue = $pValue->getExpr();
            
            if(null === $pValue)
            {
                return 'NULL';
            }
            
            return $pValue;
        }
        
        if(null === $pValue || 'NULL' === $pValue)
        {
            return 'NULL';
        }
        
        if(is_array($pValue))
        {
            foreach($pValue as &$value)
            {
                $value = $this->quote($value, (null === $pType ? $this->getParamType($value) : $pType));
            }
            
            return implode(', ', $pValue);
        }
        
        return $this->_connection->quote($pValue, (null === $pType ? $this->getParamType($pValue): $pType));
    }
    
    /**
     * @param SQLInterface|string $pSQL
     * @return integer
     */
    public function exec($pSQL)
    {
        if($pSQL instanceof SQLInterface)
        {
            $pSQL = $pSQL->getQuery();
        }
        
        $this->dispatch(new DBEvent(DBEvent::PRE_QUERY, $pSQL));
        $time = microtime(true);
        $result = $this->_connection->exec($pSQL);
        $this->dispatch(new DBEvent(DBEvent::QUERY, $pSQL, [], microtime(true) - $time));
        
        return $result;
    }

    /**
     * @see DBInterface::query()
     */
    public function query($pSQL, array $pValues = [], array $pOptions = [])
    {
        if($pSQL instanceof SQLInterface)
        {
            $pValues = array_merge($pValues, $pSQL->getBindValues());
            $pSQL = $pSQL->getQuery();
        }
        
        $SQL = $pSQL;
        $values = [];
        
        if(count($pValues) > 0)
        {
            $c = 0;
            
            foreach($pValues as $key => $value)
            {
                $isInt = is_int($key);
                
                if(!$isInt && substr($key, 0, 1) != ':')
                {
                    $key = ':' . $key;
                }
                
                if(is_array($value))
                {
                    $keys = [];
                    $pos = 0;
                    
                    foreach($value as $v)
                    {
                        if(!$isInt)
                        {
                            do
                            {
                                $k = $key . '_' . ++$pos;
                            }
                            while(array_key_exists($k, $pValues));

                            $values[$k] = $v;
                            $keys[] = $k;
                        }
                        else
                        {
                            array_splice($values, ($c + (++$pos)), 0, $v);
                            $keys[] = '?';
                        }
                    }
                    
                    if($isInt)
                    {
                        $SQL = $this->findAndReplace($SQL, implode(', ', $keys), $c);
                    }
                    else
                    {
                        $SQL = preg_replace('/' . $key . '/', implode(', ', $keys), $SQL, 1);
                    }
                }
                else if($value instanceof Expr)
                {
                    if($isInt)
                    {
                        $SQL = $this->findAndReplace($SQL, $value->getExpr(), $c);
                    }
                    else
                    {
                        $SQL = preg_replace('/' . $key . '/', $value->getExpr(), $SQL, 1);
                    }
                }
                else
                {
                    $values[$key] = $value;
                }
                
                $c++;
            }
        }
        
        $stmt = $this->_connection->prepare($SQL, $pOptions);
        
        foreach($values as $key => $value)
        {
            if($isInt)
            {
                $key = $key + 1;
            }
            
            $stmt->bindValue($key, $value, $this->getParamType($value));
        }
        
        $this->dispatch(new DBEvent(DBEvent::PRE_QUERY, $SQL, $values));
        $time = microtime(true);
        $result = $stmt->execute();
        $this->dispatch(new DBEvent(DBEvent::QUERY, $SQL, $values, microtime(true) - $time));
        
        if(!$result)
        {
            return false;
        }
        
        return new ResultSet($stmt);
    }
    
    /**
     * @see DBInterface::lastInsertId()
     */
    public function lastInsertId() 
    {
        $name = null;
            
        if(func_num_args() > 0)
        {
            $name = func_get_arg(0);
        }
        
        return $this->_connection->lastInsertId($name);
    }
    
    /**
     * @param string $pSQL
     * @param string $pValue
     * @param integer $pNth
     * @return string
     */
    protected function findAndReplace($pSQL, $pValue, $pNth) 
    { 
        if(preg_match_all('/\?/', $pSQL, $matches, PREG_OFFSET_CAPTURE))
        {
            if(array_key_exists($pNth, $matches[0]))
            { 
                $pSQL = substr($pSQL, 0, $matches[0][$pNth][1]). $pValue . substr($pSQL, $matches[0][$pNth][1] + strlen($matches[0][$pNth][0])); 
            }   
        }
        
        return $pSQL;
    } 
    
    /**
     * @param mixed $pValue
     * @return integer
     */
    protected function getParamType($pValue)
    {
        if(is_int($pValue) || is_float($pValue))
        {
            return \PDO::PARAM_INT;
        }
        else if(is_bool($pValue))
        {
           return \PDO::PARAM_BOOL;
        }
        else if(is_null($pValue))
        {
            return \PDO::PARAM_NULL;
        }
        
        return \PDO::PARAM_STR;
    }
    
    /**
     * @param string $pMethod
     * @param array $pArguments
     * @return mixed
     */
    public function __call($pMethod, $pArguments)
    {
        $context = $this->_connection;
        
        if($pMethod == 'beginTransaction')
        {
            $context = $this;
            $pMethod = 'begin';
        }
        
        return call_user_func_array([$context, $pMethod], $pArguments);
    }
}
