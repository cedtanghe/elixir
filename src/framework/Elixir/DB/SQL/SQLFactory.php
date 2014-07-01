<?php

namespace Elixir\DB\SQL;

use Elixir\DB\SQL\Create;
use Elixir\DB\SQL\Delete;
use Elixir\DB\SQL\Drop;
use Elixir\DB\SQL\Insert;
use Elixir\DB\SQL\MySQL\Create as MySQLCreate;
use Elixir\DB\SQL\MySQL\Delete as MySQLDelete;
use Elixir\DB\SQL\MySQL\Drop as MySQLDrop;
use Elixir\DB\SQL\MySQL\Insert as MySQLInsert;
use Elixir\DB\SQL\MySQL\Update as MySQLUpdate;
use Elixir\DB\SQL\Select;
use Elixir\DB\SQL\SQLite\Create as SQLiteCreate;
use Elixir\DB\SQL\SQLite\Delete as SQLiteDelete;
use Elixir\DB\SQL\SQLite\Drop as SQLiteDrop;
use Elixir\DB\SQL\SQLite\Insert as SQLiteInsert;
use Elixir\DB\SQL\SQLite\Select as SQLiteSelect;
use Elixir\DB\SQL\SQLite\Update as SQLiteUpdate;
use Elixir\DB\SQL\Update;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class SQLFactory
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
     * @return Create
     */
    public static function create($pTable = null, $pDriver = self::DRIVER_MYSQL)
    {
        switch(strtolower($pDriver))
        {
            case self::DRIVER_MYSQL:
                return new MySQLCreate($pTable);
            break;
            case self::DRIVER_SQLITE:
                return new SQLiteCreate($pTable);
            break;
        }
        
        return new Create($pTable);
    }
    
    /**
     * @param string $pTable
     * @param string $pDriver
     * @return Drop
     */
    public static function drop($pTable = null, $pDriver = self::DRIVER_MYSQL)
    {
        switch(strtolower($pDriver))
        {
            case self::DRIVER_MYSQL:
                return new MySQLDrop($pTable);
            break;
            case self::DRIVER_SQLITE:
                return new SQLiteDrop($pTable);
            break;
        }
        
        return new Drop($pTable);
    }
}
