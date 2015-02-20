<?php

namespace Elixir\DB\ResultSet;

use Elixir\DB\ResultSet\SetAbstract;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class PDO extends SetAbstract
{
    /**
     * @param integer $fetchStyle
     * @return integer
     */
    protected function convert($fetchStyle)
    {
        switch ($fetchStyle) 
        {
            case self::FETCH_ASSOC:
                return \PDO::FETCH_ASSOC;
            case self::FETCH_OBJ:
                return \PDO::FETCH_OBJ;
            case self::FETCH_NUM:
                return \PDO::FETCH_NUM;
            case self::FETCH_BOTH:
                return \PDO::FETCH_BOTH;
            case self::FETCH_DEFAULT:
                return \PDO::ATTR_DEFAULT_FETCH_MODE;
        }

        return $fetchStyle;
    }

    /**
     * @see SetAbstract::fetch()
     */
    public function fetch($fetchStyle = self::FETCH_DEFAULT) 
    {
        if (func_num_args() <= 1)
        {
            return $this->resource->fetch($this->convert($fetchStyle));
        }

        $args = func_get_args();
        $args[0] = $this->convert($args[0]);

        return call_user_func_array([$this->resource, 'fetch'], $args);
    }

    /**
     * @see SetAbstract::fetchAll()
     */
    public function fetchAll($fetchStyle = self::FETCH_ASSOC) 
    {
        if (func_num_args() <= 1) 
        {
            return $this->resource->fetchAll($this->convert($fetchStyle));
        }

        $args = func_get_args();
        $args[0] = $this->convert($args[0]);

        return call_user_func_array([$this->resource, 'fetchAll'], $args);
    }

    /**
     * @see SetAbstract::fetchColumn()
     */
    public function fetchColumn($column = 0) 
    {
        return $this->resource->fetchColumn($column);
    }

    /**
     * @see SetAbstract::fetchObject()
     */
    public function fetchObject($className = 'stdClass', array $args = [])
    {
        return $this->resource->fetchObject($className, $args);
    }

    /**
     * @see SetAbstract::fetchAssoc()
     */
    public function fetchAssoc() 
    {
        return $this->fetch(self::FETCH_ASSOC);
    }

    /**
     * @see SetAbstract::rowCount()
     */
    public function rowCount() 
    {
        return $this->resource->rowCount();
    }

    /**
     * @ignore
     */
    public function __call($method, $arguments) 
    {
        return call_user_func_array([$this->resource, $method], $arguments);
    }
}
