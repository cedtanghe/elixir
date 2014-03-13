<?php

namespace Elixir\DB\ORM;

use Elixir\DB\DBInterface;
use Elixir\DI\ContainerInterface;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
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
     * @return Select
     */
    public function select();
    
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
    public function update(array $pMembers = array(), array $pOmitMembers = array());
    
    /**
     * @return boolean
     */
    public function delete();
}