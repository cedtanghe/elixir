<?php

namespace Elixir\DB;

use Elixir\DB\ResultSet\SetAbstract;
use Elixir\DB\Query\SQL\SQLInterface;
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
     * @param SQLInterface|string $SQL
     * @return integer
     */
    public function exec($SQL);

    /**
     * @param SQLInterface|string $SQL
     * @param array $values
     * @param array $options
     * @return SetAbstract|boolean
     */
    public function query($SQL, array $values = [], array $options = []);
}
