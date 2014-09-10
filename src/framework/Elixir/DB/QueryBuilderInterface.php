<?php

namespace Elixir\DB;

use Elixir\DB\SQL\AlterTable;
use Elixir\DB\SQL\CreateTable;
use Elixir\DB\SQL\Delete;
use Elixir\DB\SQL\DropTable;
use Elixir\DB\SQL\Insert;
use Elixir\DB\SQL\Select;
use Elixir\DB\SQL\TruncateTable;
use Elixir\DB\SQL\Update;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface QueryBuilderInterface
{
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
     * @param string $pTable
     * @return TruncateTable
     */
    public function createTruncateTable($pTable = null);
}
