<?php

namespace Elixir\DB\Query\SQL;

use Elixir\DB\Query\SQL\Expr;
use Elixir\DB\Query\SQL\SQLInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
abstract class SQLAbstract implements SQLInterface
{
    /**
     * @param mixed $param
     * @return mixed
     */
    public static function protect($param)
    {
        if ($param instanceof Expr) 
        {
            $param = $param->getExpr();

            if (null === $param)
            {
                return 'NULL';
            }

            return $param;
        }

        if (is_array($param)) 
        {
            foreach ($param as &$value) 
            {
                $value = static::protect($value);
            }

            return implode(', ', $param);
        }

        if (is_int($param)) 
        {
            return (int) $param;
        }

        if (is_float($param)) 
        {
            return sprintf('%F', $param);
        }

        if (null === $param)
        {
            return 'NULL';
        }

        return '\'' . addcslashes($param, "\000\n\r\\'\"\032") . '\'';
    }

    /**
     * @var callable
     */
    protected $quoteMethod = '\Elixir\DB\Query\SQL\SQLAbstract::protect';

    /**
     * @var array
     */
    protected $bindValues = [];

    /**
     * @see SQLInterface::setQuoteMethod()
     */
    public function setQuoteMethod(callable $value) 
    {
        $this->quoteMethod = $value;
    }

    /**
     * @see SQLInterface::getQuoteMethod()
     */
    public function getQuoteMethod() 
    {
        return $this->quoteMethod;
    }

    /**
     * @see SQLInterface::quote()
     */
    public function quote($param) 
    {
        return call_user_func_array($this->quoteMethod, [$param]);
    }

    /**
     * @see SQLInterface::bindValue()
     */
    public function bindValue($key, $value) 
    {
        $this->bindValues[$key] = $value;
    }

    /**
     * @see SQLInterface::getBindValues()
     */
    public function getBindValues() 
    {
        return $this->bindValues;
    }

    /**
     * @see SQLInterface::assemble()
     */
    public function assemble($SQL, $param = null)
    {
        if (null !== $param)
        {
            if (1 == substr_count($SQL, '?'))
            {
                $isUniqArrayParameter = true;

                foreach ((array)$param as $key => $value) 
                {
                    if (!is_int($key) || $value instanceof Expr) 
                    {
                        $isUniqArrayParameter = false;
                        break;
                    }
                }

                if ($isUniqArrayParameter) 
                {
                    $param = [$param];
                }
            }

            $keys = [];
            $values = [];
            $limit = -1;

            foreach ($param as $key => $value)
            {
                if (is_string($key)) 
                {
                    if (substr($key, 0, 1) != ':') 
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

                if (!$value instanceof Expr) 
                {
                    $value = $this->quote($value);
                }

                $values[] = $value;
            }

            $query = preg_replace($keys, $values, $SQL, $limit);
            return $query;
        }

        return $SQL;
    }

    /**
     * @see SQLInterface::getQuery()
     */
    public function getQuery() 
    {
        return $this->render();
    }

    /**
     * @ignore
     */
    public function __toString() 
    {
        return $this->getQuery();
    }
}
