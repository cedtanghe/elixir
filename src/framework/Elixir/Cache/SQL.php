<?php

namespace Elixir\Cache;

use Elixir\Cache\CacheAbstract;
use Elixir\DB\DBInterface;
use Elixir\DB\Query\QueryBuilderFactory;
use Elixir\DB\Query\QueryBuilderInterface;
use Elixir\DB\Query\SQL\ColumnFactory;
use Elixir\DB\Query\SQL\ConstraintFactory;
use Elixir\DB\Query\SQL\CreateTable;
use Elixir\DB\Query\SQL\MySQL\CreateTable as MySQLCreateTable;
use Elixir\DB\Query\SQL\SQLite\CreateTable as SQLiteCreateTable;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class SQL extends CacheAbstract
{
    /**
     * @param string $table
     * @param string $driver
     * @return CreateTable
     * @throws \RuntimeException
     */
    public static function build($table = 'cache_metas', $driver = QueryBuilderInterface::DRIVER_MYSQL)
    {
        $create = QueryBuilderFactory::createTable($table, $driver);
        
        switch ($driver)
        {
            case QueryBuilderInterface::DRIVER_MYSQL:
                static::tableMySQL($create);
                break;
            case QueryBuilderInterface::DRIVER_SQLITE:
                static::tableSQLite($create);
                break;
            default:
                throw new \RuntimeException(
                    sprintf('Table creation for driver "%s" has not yet been implemented.', $driver)
                );
        }
        
        return $create;
    }
    
    /**
     * @param MySQLCreateTable $create
     */
    protected static function tableMySQL(MySQLCreateTable &$create)
    {
        $create->ifNotExists(true)
               ->column(ColumnFactory::varchar('key')->setCollating('utf8_general_ci'))
               ->column(ColumnFactory::text('value')->setCollating('utf8_general_ci'))
               ->column(ColumnFactory::int('ttl'))
               ->constraint(ConstraintFactory::unique('key'))
               ->option(CreateTable::OPTION_ENGINE, CreateTable::ENGINE_INNODB)
               ->option(CreateTable::OPTION_CHARSET, CreateTable::CHARSET_UTF8);
    }
    
    /**
     * @param SQLiteCreateTable $create
     */
    protected static function tableSQLite(SQLiteCreateTable &$create)
    {
        $create->ifNotExists(true)
               ->column(ColumnFactory::text('key'))
               ->column(ColumnFactory::text('value'))
               ->column(ColumnFactory::int('ttl'))
               ->constraint(ConstraintFactory::unique('key'));
    }

    /**
     * @var string
     */
    const DEFAULT_ENCODER = '\Elixir\Cache\Encoder\Serialize';
    
    /**
     * @var DBInterface
     */
    protected $DB;
    
    /**
     * @var string 
     */
    protected $table;
    
    /**
     * @param DBInterface $DB
     * @param string $table
     * @throws \InvalidArgumentException
     */
    public function __construct(DBInterface $DB, $table) 
    {
        if (!$DB instanceof QueryBuilderInterface)
        {
            throw new \InvalidArgumentException(
                'This class requires the db object implements the interface "\Elixir\DB\Query\QueryBuilderInterface" for convenience.'
            );
        }
        
        $this->DB = $DB;
        $this->table = $table;
    }
    
    /**
     * @return DBInterface
     */
    public function getDB()
    {
        return $this->DB;
    }
    
    /**
     * @return string
     */
    public function getStockageName()
    {
        return $this->table;
    }

        /**
     * @see CacheAbstract::getEncoder()
     */
    public function getEncoder() 
    {
        if (null === $this->encoder) 
        {
            $class = self::DEFAULT_ENCODER;
            $this->setEncoder(new $class());
        }

        return parent::getEncoder();
    }

    /**
     * @see CacheAbstract::has()
     */
    public function has($key) 
    {
        return null !== $this->get($key, null);
    }

    /**
     * @see CacheAbstract::get()
     */
    public function get($key, $default = null) 
    {
        $stmt = $this->DB->query(
            $this->DB->createSelect($this->table)->where('key = ?', $key)
        );
        
        $row = $stmt->one();
        
        if (false !== $row)
        {
            $expired = time() > $row['ttl'];

            if ($expired) 
            {
                $this->remove($key);
                return is_callable($default) ? call_user_func($default) : $default;
            }
            
            return $this->getEncoder()->decode($data['value']);
        }

        return is_callable($default) ? call_user_func($default) : $default;
    }

    /**
     * @see CacheAbstract::set()
     */
    public function set($key, $value, $ttl = self::DEFAULT_TTL)
    {
        $value = $this->getEncoder()->encode($value);
        $ttl = time() + $this->parseTimeToLive($ttl);
        
        try
        {
            $sql = $this->DB->createInsert($this->table);
            $sql->values([
                'key' => $key,
                'value' => $value,
                'ttl' => $ttl
            ]);
            
            if (method_exists($this, 'duplicateKeyUpdate'))
            {
                $sql->duplicateKeyUpdate([
                    'value' => $value,
                    'ttl' => $ttl
                ]);
            }
            
            $this->DB->exec($sql);
        } 
        catch (\Exception $exception)
        {
            $this->DB->exec(
                $this->DB->createUpdate($this->table)
                ->set([
                    'value' => $value,
                    'ttl' => $ttl
                ])
                ->where('key = ?', $key)
            );
        }
        
        return true;
    }
    
    /**
     * @see CacheAbstract::remove()
     */
    public function remove($key) 
    {
        $this->DB->exec(
            $this->DB->createDelete($this->table)
            ->where('key = ?', $key)
        );
        
        return true;
    }

    /**
     * @see CacheAbstract::incremente()
     */
    public function incremente($key, $step = 1) 
    {
        $value = $this->get($key, null);
        
        if (null === $value)
        {
            return 0;
        }
        
        $value = (int)$value + $step;
        
        $this->DB->exec(
            $this->DB->createUpdate($this->table)
            ->set(['value' => $this->getEncoder()->encode($value)])
            ->where('key = ?', $key)
        );
        
        return $value;
    }

    /**
     * @see CacheAbstract::decremente()
     */
    public function decremente($key, $step = 1) 
    {
        $value = $this->get($key, null);
        
        if (null === $value)
        {
            return 0;
        }
        
        $value = (int)$value - $step;
        
        $this->DB->exec(
            $this->DB->createUpdate($this->table)
            ->set(['value' => $this->getEncoder()->encode($value)])
            ->where('key = ?', $key)
        );
        
        return $value;
    }
    
    /**
     * @see CacheAbstract::flush()
     */
    public function flush()
    {
        try
        {
            $this->DB->exec(
                $this->DB->createTruncateTable($this->table)
            );
        } 
        catch (\Exception $exception)
        {
            $this->DB->exec(
                $this->DB->createDelete($this->table)
            );
        }
        
        return true;
    }
}
