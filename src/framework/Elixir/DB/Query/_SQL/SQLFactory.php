<?php

namespace Elixir\DB\Query\SQL;

use Elixir\DB\Query\SQL\AlterTable;
use Elixir\DB\Query\SQL\CreateTable;
use Elixir\DB\Query\SQL\Delete;
use Elixir\DB\Query\SQL\DropTable;
use Elixir\DB\Query\SQL\Insert;
use Elixir\DB\Query\SQL\MySQL\AlterTable as MySQLAlterTable;
use Elixir\DB\Query\SQL\MySQL\CreateTable as MySQLCreateTable;
use Elixir\DB\Query\SQL\MySQL\Delete as MySQLDelete;
use Elixir\DB\Query\SQL\MySQL\DropTable as MySQLDropTable;
use Elixir\DB\Query\SQL\MySQL\Insert as MySQLInsert;
use Elixir\DB\Query\SQL\MySQL\Update as MySQLUpdate;
use Elixir\DB\Query\SQL\Select;
use Elixir\DB\Query\SQL\SQLite\AlterTable as SQLiteAlterTable;
use Elixir\DB\Query\SQL\SQLite\CreateTable as SQLiteCreateTable;
use Elixir\DB\Query\SQL\SQLite\Delete as SQLiteDelete;
use Elixir\DB\Query\SQL\SQLite\DropTable as SQLiteDropTable;
use Elixir\DB\Query\SQL\SQLite\Insert as SQLiteInsert;
use Elixir\DB\Query\SQL\SQLite\Select as SQLiteSelect;
use Elixir\DB\Query\SQL\SQLite\Update as SQLiteUpdate;
use Elixir\DB\Query\SQL\Update;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class QueryBuilderFactory
{
    /**
     * @var string
     */
    const DRIVER_MYSQL = 'mysql';
    
    /**
     * @var string
     */
    const DRIVER_SQLITE = 'sqlite';
    
    /**
     * @param string $pTable
     * @param string $pDriver
     * @return Select
     */
    public static function select($pTable = null, $pDriver = self::DRIVER_MYSQL)
    {
        switch(strtolower($pDriver))
        {
            case self::DRIVER_SQLITE:
                return new SQLiteSelect($pTable);
            break;
        }
        
        return new Select($pTable);
    }
    
    /**
     * @param string $pTable
     * @param string $pDriver
     * @return Insert
     */
    public static function insert($pTable = null, $pDriver = self::DRIVER_MYSQL)
    {
        switch(strtolower($pDriver))
        {
            case self::DRIVER_MYSQL:
                return new MySQLInsert($pTable);
            break;
            case self::DRIVER_SQLITE:
                return new SQLiteInsert($pTable);
            break;
        }
        
        return new Insert($pTable);
    }
    
    /**
     * @param string $pTable
     * @param string $pDriver
     * @return Update
     */
    public static function update($pTable = null, $pDriver = self::DRIVER_MYSQL)
    {
        switch(strtolower($pDriver))
        {
            case self::DRIVER_MYSQL:
                return new MySQLUpdate($pTable);
            break;
            case self::DRIVER_SQLITE:
                return new SQLiteUpdate($pTable);
            break;
        }
        
        return new Update($pTable);
    }
    
    /**
     * @param string $pTable
     * @param string $pDriver
     * @return Delete
     */
    public static function delete($pTable = null, $pDriver = self::DRIVER_MYSQL)
    {
        switch(strtolower($pDriver))
        {
            case self::DRIVER_MYSQL:
                return new MySQLDelete($pTable);
            break;
            case self::DRIVER_SQLITE:
                return new SQLiteDelete($pTable);
            break;
        }
        
        return new Delete($pTable);
    }
    
    /**
     * @param string $pTable
     * @param string $pDriver
     * @return CreateTable
     */
    public static function createTable($pTable = null, $pDriver = self::DRIVER_MYSQL)
    {
        switch(strtolower($pDriver))
        {
            case self::DRIVER_MYSQL:
                return new MySQLCreateTable($pTable);
            break;
            case self::DRIVER_SQLITE:
                return new SQLiteCreateTable($pTable);
            break;
        }
        
        return new CreateTable($pTable);
    }
    
    /**
     * @param string $pTable
     * @param string $pDriver
     * @return AlterTable
     */
    public static function createAlterTable($pTable = null, $pDriver = self::DRIVER_MYSQL)
    {
        switch(strtolower($pDriver))
        {
            case self::DRIVER_MYSQL:
                return new MySQLAlterTable($pTable);
            break;
            case self::DRIVER_SQLITE:
                return new SQLiteAlterTable($pTable);
            break;
        }
        
        return new AlterTable($pTable);
    }
    
    /**
     * @param string $pTable
     * @param string $pDriver
     * @return DropTable
     */
    public static function dropTable($pTable = null, $pDriver = self::DRIVER_MYSQL)
    {
        switch(strtolower($pDriver))
        {
            case self::DRIVER_MYSQL:
                return new MySQLDropTable($pTable);
            break;
            case self::DRIVER_SQLITE:
                return new SQLiteDropTable($pTable);
            break;
        }
        
        return new DropTable($pTable);
    }
    
    /**
     * @param string $pTable
     * @param string $pDriver
     * @return TruncateTable
     */
    public static function truncateTable($pTable = null, $pDriver = self::DRIVER_MYSQL)
    {
        switch(strtolower($pDriver))
        {
            case self::DRIVER_SQLITE:
                throw new \RuntimeException('TRUNCATE TABLE command for SQLite does not exist.');
            break;
        }
        
        return new TruncateTable($pTable);
    }
}
