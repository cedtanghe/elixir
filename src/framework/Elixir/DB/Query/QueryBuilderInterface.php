<?php

namespace Elixir\DB\Query;

use Elixir\DB\Query\SQL\AlterTable;
use Elixir\DB\Query\SQL\CreateTable;
use Elixir\DB\Query\SQL\Delete;
use Elixir\DB\Query\SQL\DropTable;
use Elixir\DB\Query\SQL\Insert;
use Elixir\DB\Query\SQL\Select;
use Elixir\DB\Query\SQL\TruncateTable;
use Elixir\DB\Query\SQL\Update;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
interface QueryBuilderInterface 
{
    /**
     * @param string $table
     * @return Select
     */
    public function createSelect($table = null);

    /**
     * @param string $table
     * @return Insert
     */
    public function createInsert($table = null);

    /**
     * @param string $table
     * @return Delete
     */
    public function createDelete($table = null);

    /**
     * @param string $table
     * @return Update
     */
    public function createUpdate($table = null);

    /**
     * @param string $table
     * @return CreateTable
     */
    public function createTable($table = null);

    /**
     * @param string $table
     * @return AlterTable
     */
    public function createAlterTable($table = null);

    /**
     * @param string $table
     * @return DropTable
     */
    public function createDropTable($table = null);

    /**
     * @param string $table
     * @return TruncateTable
     */
    public function createTruncateTable($table = null);
}
