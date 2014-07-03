<?php

namespace Elixir\DB;

use Elixir\DB\Result\SetAbstract;
use Elixir\DB\SQL\AlterTable;
use Elixir\DB\SQL\CreateTable;
use Elixir\DB\SQL\Delete;
use Elixir\DB\SQL\DropTable;
use Elixir\DB\SQL\Insert;
use Elixir\DB\SQL\Select;
use Elixir\DB\SQL\SQLAbstract;
use Elixir\DB\SQL\Update;
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
     * @param string $pTable
     * @return Select
     */
    public function createSelect($pTable = null);
    
    /**
     * @param string $pTable
     * @return Insert
     */
    public function createInsert($pTable = null);
            
    /**
     * @param string $pTable
     * @return Delete
     */
    public function createDelete($pTable = null);
    
    /**
     * @param string $pTable
     * @return Update
     */
    public function createUpdate($pTable = null);
    
    /**
     * @param string $pTable
     * @return CreateTable
     */
    public function createTable($pTable = null);
    
    /**
     * @param string $pTable
     * @return AlterTable
     */
    public function createAlterTable($pTable = null);
    
    /**
     * @param string $pTable
     * @return DropTable
     */
    public function createDropTable($pTable = null);
        
    /**
     * @param mixed $pValue
     * @param integer $pType
     * @return mixed
     */
    public function quote($pValue, $pType = null);
    
    /**
     * @param SQLAbstract|string $pSQL
     * @param array $pValues
     * @param array $pOptions
     * @return SetAbstract|boolean
     */
    public function query($pSQL, array $pValues = [], array $pOptions = []);
}