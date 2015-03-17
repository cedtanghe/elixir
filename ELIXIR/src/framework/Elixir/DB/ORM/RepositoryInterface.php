<?php

namespace Elixir\DB\ORM;

use Elixir\DB\DBInterface;
use Elixir\DB\ORM\EntityInterface;
use Elixir\DI\ContainerInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
interface RepositoryInterface extends EntityInterface
{
    /**
     * @param ContainerInterface $value
     */
    public function setConnectionManager(ContainerInterface $value);

    /**
     * @return ContainerInterface
     */
    public function getConnectionManager();

    /**
     * @param string $key
     * @return DBInterface
     */
    public function getConnection($key = null);

    /**
     * @return string
     */
    public function getTable();
    
    /**
     * @return mixed
     */
    public function getPrimaryKey();

    /**
     * @return mixed
     */
    public function getPrimaryValue();

    /**
     * @return boolean
     */
    public function save();

    /**
     * @return boolean
     */
    public function insert();

    /**
     * @param array $members
     * @param array $omitMembers
     * @return boolean
     */
    public function update(array $members = [], array $omitMembers = []);

    /**
     * @return boolean
     */
    public function delete();
}
