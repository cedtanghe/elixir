<?php

namespace Elixir\DB;

use Elixir\DB\Query\QueryInterface;
use Elixir\DB\ResultSet\ResultSetAbstract;
use Elixir\Dispatcher\DispatcherInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
interface DBInterface extends DispatcherInterface 
{
    /**
     * @return integer
     */
    public function lastInsertId();

    /**
     * @return boolean
     */
    public function begin();

    /**
     * @return boolean
     */
    public function rollBack();

    /**
     * @return boolean
     */
    public function commit();

    /**
     * @return boolean
     */
    public function inTransaction();

    /**
     * @param mixed $value
     * @param integer $type
     * @return mixed
     */
    public function quote($value, $type = null);

    /**
     * @param QueryInterface|string $query
     * @return integer
     */
    public function exec($query);

    /**
     * @param QueryInterface|string $query
     * @param array $values
     * @param array $options
     * @return ResultSetAbstract|boolean
     */
    public function query($query, array $values = [], array $options = []);
}
