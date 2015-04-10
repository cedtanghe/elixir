<?php

namespace Elixir\Cache;

use Elixir\Cache\CacheAbstract;
use Elixir\DB\DBInterface;
use Elixir\DB\Query\QueryBuilderInterface;
use Elixir\DB\Query\SQL\ColumnFactory;
use Elixir\DB\Query\SQL\ConstraintFactory;
use Elixir\DB\Query\SQL\CreateTable;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class SQL extends CacheAbstract
{
    /**
     * @param string $table
     * @param string $driver
     */
    public static function build($table = 'cache_metas', $driver = QueryBuilderInterface::DRIVER_MYSQL)
    {
        // Todo
        $create = new CreateTable($table);
        
        // Key
        $create->column(
            ColumnFactory::varchar('key', 255, false)
            ->setCollating('utf8_general_ci')
        );
        
        // Value
        $create->column(
            ColumnFactory::varchar('value', 255, true)
            ->setCollating('utf8_general_ci')
        );
        
        // Expriration
        $create->column(
            ColumnFactory::varchar('expiration', 255, true)
            ->setCollating('utf8_general_ci')
        );
        
        $create->constraint(ConstraintFactory::unique('key'));
        $create->option(CreateTable::OPTION_ENGINE, CreateTable::ENGINE_INNODB);
        $create->option(CreateTable::OPTION_CHARSET, CreateTable::CHARSET_UTF8);
        
        return $create;
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
     * @see CacheAbstract::exists()
     */
    public function exists($key) 
    {
        return true;
    }

    /**
     * @see CacheAbstract::get()
     */
    public function get($key, $default = null) 
    {
        return $value;
    }

    /**
     * @see CacheAbstract::set()
     */
    public function store($key, $value, $ttl = self::DEFAULT_TTL)
    {
        if ($ttl != 0) 
        {
            $ttl = time() + $this->parseTimeToLive($ttl);
        }
        
        return true;
    }
    
    /**
     * @see CacheAbstract::delete()
     */
    public function delete($key) 
    {
        return true;
    }

    /**
     * @see CacheAbstract::incremente()
     */
    public function incremente($key, $step = 1) 
    {
        return 0;
    }

    /**
     * @see CacheAbstract::decremente()
     */
    public function decremente($key, $step = 1) 
    {
        return 0;
    }
    
    /**
     * @see CacheAbstract::flush()
     */
    public function flush()
    {
        return true;
    }
}
