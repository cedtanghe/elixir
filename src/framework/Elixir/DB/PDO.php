<?php

namespace Elixir\DB;

use Elixir\DB\DBEvent;
use Elixir\DB\DBInterface;
use Elixir\DB\Query\QueryBuilderInterface;
use Elixir\DB\Query\QueryBuilderTrait;
use Elixir\DB\Query\SQL\Expr;
use Elixir\DB\Query\SQL\SQLInterface;
use Elixir\DB\ResultSet\PDO as ResultSet;
use Elixir\Dispatcher\DispatcherTrait;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class PDO implements DBInterface, QueryBuilderInterface 
{
    use DispatcherTrait;
    use QueryBuilderTrait;

    /**
     * @var \PDO 
     */
    protected $connection;

    /**
     * @var boolean
     */
    protected $autoDestruct;

    /**
     * @var boolean
     */
    protected $hasTransaction = false;

    /**
     * @var integer
     */
    protected $countTransaction = 0;

    /**
     * @param \PDO $connection
     * @param boolean $autoDestruct
     */
    public function __construct(\PDO $connection, $autoDestruct = true)
    {
        $this->connection = $connection;
        $this->autoDestruct = $autoDestruct;

        if (method_exists($this->connection, 'inTransaction') && $this->connection->inTransaction()) 
        {
            $this->hasTransaction = true;
            $this->countTransaction = 1;
        }
    }

    /**
     * @ignore
     */
    public function __destruct() 
    {
        if ($this->autoDestruct) 
        {
            $this->connection = null;
        }
    }

    /**
     * @see QueryBuilderInterface::getDriver()
     */
    public function getDriver()
    {
        return $this->connection->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }

    /**
     * @see DBInterface::begin()
     */
    public function begin() 
    {
        $this->countTransaction++;

        if ($this->hasTransaction) 
        {
            return false;
        }

        $this->hasTransaction = $this->connection->beginTransaction();
        return $this->hasTransaction;
    }

    /**
     * @see DBInterface::rollBack()
     */
    public function rollBack() 
    {
        $this->countTransaction--;

        if ($this->countTransaction > 0) 
        {
            return false;
        }

        $this->hasTransaction = !$this->connection->rollBack();
        return !$this->hasTransaction;
    }

    /**
     * @see DBInterface::commit()
     */
    public function commit() 
    {
        $this->countTransaction--;

        if ($this->countTransaction > 0) 
        {
            return false;
        }

        $this->hasTransaction = !$this->connection->commit();
        return !$this->hasTransaction;
    }

    /**
     * @see DBInterface::inTransaction()
     */
    public function inTransaction() 
    {
        return $this->hasTransaction;
    }
    
    /**
     * @param mixed $value
     * @return integer
     */
    protected function getParamType($value) 
    {
        if (is_int($value) || is_float($value)) 
        {
            return \PDO::PARAM_INT;
        } 
        else if (is_bool($value)) 
        {
            return \PDO::PARAM_BOOL;
        } 
        else if (is_null($value)) 
        {
            return \PDO::PARAM_NULL;
        }

        return \PDO::PARAM_STR;
    }

    /**
     * @see DBInterface::quote()
     */
    public function quote($value, $type = null) 
    {
        if ($value instanceof Expr) 
        {
            $value = $value->getExpr();

            if (null === $value) 
            {
                return 'NULL';
            }

            return $value;
        }

        if (null === $value || 'NULL' === $value) 
        {
            return 'NULL';
        }

        if (is_array($value)) 
        {
            foreach ($value as &$v) 
            {
                $v = $this->quote($v, (null === $type ? $this->getParamType($v) : $type));
            }

            return implode(', ', $value);
        }

        return $this->connection->quote($value, (null === $type ? $this->getParamType($value) : $type));
    }

    /**
     * @see DBInterface::exec()
     */
    public function exec($query) 
    {
        $event = new DBEvent(
            DBEvent::PRE_QUERY, 
            ['query' => $query]
        );
        
        $this->dispatch($event);
        $query = $event->getQuery();
        
        if ($query instanceof SQLInterface) 
        {
            $query = $query->getQuery();
        }
                
        $time = microtime(true);
        $result = $this->connection->exec($query);
        
        $this->dispatch(
            new DBEvent(
                DBEvent::QUERY, 
                [
                    'query' => $query,
                    'time' => microtime(true) - $time
                ]
            )
        );

        return $result;
    }

    /**
     * @see DBInterface::query()
     */
    public function query($query, array $values = [], array $options = []) 
    {
        $findAndReplace = function($query, $value, $nth) 
        {
            if (preg_match_all('/\?/', $query, $matches, PREG_OFFSET_CAPTURE)) 
            {
                if (array_key_exists($nth, $matches[0]))
                {
                    $query = substr($query, 0, $matches[0][$nth][1]) . $value . substr($query, $matches[0][$nth][1] + strlen($matches[0][$nth][0]));
                }
            }

            return $query;
        };
        
        $event = new DBEvent(
            DBEvent::PRE_QUERY, 
            [
                'query' => $query, 
                'values' => $values
            ]
        );
        
        $this->dispatch($event);
        $query = $event->getQuery();
        $values = $event->getValues();
        
        if ($query instanceof SQLInterface) 
        {
            $values = array_merge($values, $query->getBindValues());
            $query = $query->getQuery();
        }
        
        $parsedValues = [];
        
        if (count($values) > 0) 
        {
            $c = 0;

            foreach ($values as $key => $value) 
            {
                $isInt = is_int($key);

                if (!$isInt && substr($key, 0, 1) != ':')
                {
                    $key = ':' . $key;
                }

                if (is_array($value)) 
                {
                    $keys = [];
                    $pos = 0;

                    foreach ($value as $v) 
                    {
                        if (!$isInt)
                        {
                            do 
                            {
                                $k = $key . '_' . ++$pos;
                            } 
                            while (array_key_exists($k, $values));

                            $parsedValues[$k] = $v;
                            $keys[] = $k;
                        } 
                        else 
                        {
                            array_splice($parsedValues, ($c + (++$pos)), 0, $v);
                            $keys[] = '?';
                        }
                    }

                    if ($isInt) 
                    {
                        $query = $findAndReplace($query, implode(', ', $keys), $c);
                    } 
                    else 
                    {
                        $query = preg_replace('/' . $key . '/', implode(', ', $keys), $query, 1);
                    }
                } 
                else if ($value instanceof Expr) 
                {
                    if ($isInt) 
                    {
                        $query = $findAndReplace($query, $value->getExpr(), $c);
                    } 
                    else
                    {
                        $query = preg_replace('/' . $key . '/', $value->getExpr(), $query, 1);
                    }
                } 
                else 
                {
                    $parsedValues[$key] = $value;
                }
                
                $c++;
            }
        }

        $stmt = $this->connection->prepare($query, $options);

        foreach ($parsedValues as $key => $value) 
        {
            if ($isInt)
            {
                $key = $key + 1;
            }

            $stmt->bindValue($key, $value, $this->getParamType($value));
        }
        
        $time = microtime(true);
        $result = $stmt->execute();
        
        $this->dispatch(
            new DBEvent(
                DBEvent::QUERY, 
                [
                    'query' => $query, 
                    'values' => $parsedValues,
                    'time' => microtime(true) - $time
                ]
            )
        );
        
        if (!$result) 
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

        if (func_num_args() > 0) 
        {
            $name = func_get_arg(0);
        }

        return $this->connection->lastInsertId($name);
    }
    
    /**
     * @ignore
     */
    public function __call($method, $arguments)
    {
        $context = $this->connection;

        if ($method == 'beginTransaction') 
        {
            $context = $this;
            $method = 'begin';
        }

        return call_user_func_array([$context, $method], $arguments);
    }
}
