<?php

namespace Elixir\DB\ORM;

use Elixir\DB\DBInterface;
use Elixir\DB\ObjectMapper\EntityInterface;
use Elixir\DB\Query\SQL\SQLite\Select;
use Elixir\DI\ContainerInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface RepositoryInterface extends EntityInterface
{
    /**
     * @param ContainerInterface $pValue
     */
    public function setConnectionManager(ContainerInterface $pValue);
    
    /**
     * @return ContainerInterface
     */
    public function getConnectionManager();
    
    /**
     * @param string $pKey
     * @return DBInterface
     */
    public function getConnection($pKey = null);
    
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
     * @param string $pAlias
     * @return Select
     */
    public function select($pAlias = null);
    
    /**
     * @return boolean
     */
    public function save();
    
    /**
     * @return boolean
     */
    public function insert();
    
    /**
     * @param array $pMembers
     * @param array $pOmitMembers
     * @return boolean
     */
    public function update(array $pMembers = [], array $pOmitMembers = []);
    
    /**
     * @return boolean
     */
    public function delete();
}
