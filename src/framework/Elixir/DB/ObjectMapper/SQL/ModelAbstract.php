<?php

namespace Elixir\DB\ObjectMapper\SQL;

use Elixir\DB\DBInterface;
use Elixir\DB\ObjectMapper\Collection;
use Elixir\DB\ObjectMapper\EntityAbstract;
use Elixir\DB\ObjectMapper\EntityInterface;
use Elixir\DB\ObjectMapper\RelationInterface;
use Elixir\DB\ObjectMapper\RepositoryEvent;
use Elixir\DB\ObjectMapper\RepositoryInterface;
use Elixir\DB\ObjectMapper\SQL\Select;
use Elixir\DB\Query\QueryBuilderInterface;
use Elixir\DB\Query\SQL\SQLInterface;
use Elixir\DI\ContainerInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
abstract class ModelAbstract extends EntityAbstract implements RepositoryInterface 
{
    /**
     * @var string
     */
    const DEFAULT_CONNECTION_KEY = 'db.default';
    
    /**
     * @var ContainerInterface
     */
    public static $defaultConnectionManager;

    /**
     * @var ContainerInterface
     */
    protected $connectionManager;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var mixed
     */
    protected $primaryKey = 'id';

    /**
     * @var boolean
     */
    protected $autoIncrement = true;
    
    /**
     * @var array
     */
    protected $related = [];

    /**
     * @see EntityAbstract::__construct()
     */
    public function __construct(array $config = null) 
    {
        if (isset($config['connection_manager']))
        {
            $this->setConnectionManager($config['connection_manager']);
            unset($config['connection_manager']);
        }
        
        parent::__construct($config);
        
        if (null === $this->table) 
        {
            $this->table = lcfirst(pathinfo($this->className, PATHINFO_BASENAME));
        }
    }

    /**
     * @see RepositoryInterface::setConnectionManager()
     */
    public function setConnectionManager(ContainerInterface $value) 
    {
        $this->connectionManager = $value;
        
        if (null === self::$defaultConnectionManager) 
        {
            self::$defaultConnectionManager = $this->connectionManager;
        }
    }

    /**
     * @see RepositoryInterface::getConnectionManager()
     */
    public function getConnectionManager() 
    {
        return $this->connectionManager ?: self::$defaultConnectionManager;
    }
    
    /**
     * @see RepositoryInterface::getConnection()
     * @return DBInterface
     * @return QueryBuilderInterface
     */
    public function getConnection($key) 
    {
        if (!$this->connectionManager->has($key)) 
        {
            $key = self::DEFAULT_CONNECTION_KEY;
        }

        return $this->connectionManager->get($key);
    }

    /**
     * @see RepositoryInterface::getStockageName()
     */
    public function getStockageName()
    {
        return $this->table;
    }
    
    /**
     * @return boolean
     */
    public function isAutoIncrement() 
    {
        return $this->autoIncrement && null !== $this->primaryKey;
    }
    
    /**
     * @see RepositoryInterface::getPrimaryKey()
     */
    public function getPrimaryKey() 
    {
        return $this->primaryKey;
    }
    
    /**
     * @return array
     */
    public function getRelatedKeys()
    {
        return array_keys($this->related);
    }

    /**
     * @param string $key
     * @return string
     */
    public function getRelatedType($key) 
    {
        return isset($this->related[$key]) ? $this->related[$key] : null;
    }
    
    /**
     * @param string $key
     * @return RelationInterface
     * @throws \InvalidArgumentException
     */
    public function relation($key)
    {
        if (array_key_exists($key, $this->related))
        {
            return $this->get($key);
        }
        
        throw new \InvalidArgumentException(sprintf('Property "%so" is not a relationship.', $key));
    }
    
    /**
     * @see RepositoryInterface::find()
     */
    public function find($options = null)
    {
        return new Select($this, $options);
    }
    
    /**
     * @return boolean
     */
    public function exist()
    {
        if (null === $this->primaryKey) 
        {
            return false;
        }

        foreach ((array)$this->primaryKey as $key) 
        {
            if ($this->ignoreValue === $this->$key)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * @see RepositoryInterface::save()
     */
    public function save() 
    {
        return $this->exist() ? $this->update() : $this->insert();
    }

    /**
     * @see RepositoryInterface::insert()
     * @throws \LogicException
     */
    public function insert() 
    {
        if ($this->isReadOnly())
        {
            return false;
        }
        
        $this->dispatch(new RepositoryEvent(RepositoryEvent::PRE_INSERT));

        $DB = $this->getConnection('db.write');
        $data = [];

        foreach ($this->fillable as $column) 
        {
            $data[$column] = $this->get($column);
        }

        $values = [];

        foreach ($data as $key => $value) 
        {
            if ($this->ignoreValue !== $value) 
            {
                $values['`' . $key . '`'] = $value;
            }
        }
        
        $query = $DB->createInsert('`' . $this->table . '`');
        $query->values($values, SQLInterface::VALUES_SET);

        $event = new RepositoryEvent(
            RepositoryEvent::PARSE_QUERY_INSERT, 
            ['query' => $query]
        );
        
        $this->dispatch($event);
        $query = $event->getQuery();

        $result = $DB->exec($query);
        $result = $result > 0;

        if ($result) 
        {
            if ($this->autoIncrement && null !== $this->primaryKey)
            {
                if (is_array($this->primaryKey))
                {
                    throw new \LogicException('It is impossible to increment several primary keys.');
                }

                $this->{$this->primaryKey} = $DB->lastInsertId();
            }
        }

        $this->dispatch(new RepositoryEvent(RepositoryEvent::INSERT));
        $this->sync(self::SYNC_FILLABLE);

        return $result;
    }

    /**
     * @see RepositoryInterface::update()
     * @throws \LogicException
     */
    public function update(array $members = [], array $omitMembers = []) 
    {
        if ($this->isReadOnly()) 
        {
            return false;
        }

        $this->dispatch(new RepositoryEvent(RepositoryEvent::PRE_UPDATE));

        if (!$this->isModified(self::SYNC_FILLABLE)) 
        {
            $this->dispatch(new RepositoryEvent(RepositoryEvent::UPDATE));
            return true;
        }

        $DB = $this->getConnection('db.write');
        $data = [];

        foreach (array_keys($this->getModified(self::SYNC_FILLABLE)) as $column) 
        {
            if (in_array($column, $omitMembers) || (count($members) > 0 && !in_array($column, $members))) 
            {
                continue;
            }

            $data[$column] = $this->get($column);
        }

        $values = [];

        foreach ($data as $key => $value)
        {
            if ($this->ignoreValue !== $value) 
            {
                $values['`' . $key . '`'] = $value;
            }
        }

        if (count($values) == 0) 
        {
            $this->dispatch(new RepositoryEvent(RepositoryEvent::UPDATE));
            return true;
        }

        $query = $DB->createUpdate('`' . $this->table . '`');
        $query->set($values, SQLInterface::VALUES_SET);

        if (null === $this->primaryKey) 
        {
            throw new \LogicException('No primary key is defined');
        }

        foreach ((array)$this->primaryKey as $key)
        {
            $query->where(sprintf('`%s`.`%s` = ?', $this->table, $key), $this->get($key));
        }
        
        $event = new RepositoryEvent(
            RepositoryEvent::PARSE_QUERY_UPDATE, 
            ['query' => $query]
        );
        
        $this->dispatch($event);
        $query = $event->getQuery();

        $result = $DB->exec($query);
        $result = $result > 0;
        
        $this->dispatch(new RepositoryEvent(RepositoryEvent::UPDATE));
        $this->sync(self::SYNC_FILLABLE);

        return $result;
    }

    /**
     * @see RepositoryInterface::delete()
     * @throws \LogicException
     */
    public function delete() 
    {
        if ($this->isReadOnly()) 
        {
            return false;
        }

        $this->dispatch(new RepositoryEvent(RepositoryEvent::PRE_DELETE));

        $DB = $this->getConnection('db.write');
        $query = $DB->createDelete('`' . $this->table . '`');

        if (null === $this->primaryKey) 
        {
            throw new \LogicException('No primary key is defined.');
        }

        foreach ((array)$this->primaryKey as $key) 
        {
            $query->where(sprintf('`%s`.`%s` = ?', $this->table, $key), $this->get($key));
        }
        
        $event = new RepositoryEvent(
            RepositoryEvent::PARSE_QUERY_DELETE, 
            ['query' => $query]
        );
        
        $this->dispatch($event);
        $query = $event->getQuery();

        $result = $DB->exec($query);
        $result = $result > 0;

        $this->dispatch(new RepositoryEvent(RepositoryEvent::DELETE));

        foreach ($this->data as $key => $value)
        {
            $this->set($key, $this->_ignoreValue);
        }

        $this->sync(self::SYNC_FILLABLE);
        return $result;
    }
    
    /**
     * @see EntityAbstract::set()
     */
    public function set($key, $value) 
    {
        parent::set($key, $value);
        
        if (array_key_exists($key, $this->related))
        {
            $relation = $this->get($key);
            $relation->setRelated($value, ['filled' => $value !== $this->ignoreValue]);
            
            $this->data[$key] = $relation;
        } 
        else if ($value instanceof RelationInterface) 
        {
            $this->related[$key] = $value->getType();
        }
    }
    
    /**
     * @see EntityAbstract::createInstance()
     */
    protected function createInstance($class, array $config = null)
    {
        $instance = $class::factory($config);
        $instance->setConnectionManager($this->connectionManager);
        
        return $instance;
    }
    
    /**
     * @see EntityAbstract::export()
     */
    public function export(array $members = [], array $omitMembers = [], array $options = [])
    {
        $options = array_merge(
            [
                'raw' => true,
                'format' => self::FORMAT_PHP
            ],
            $options
        );
        
        $data = [];

        foreach (array_keys($this->data) as $key)
        {
            if (in_array($key, $omitMembers) || (count($members) > 0 && !in_array($key, $members)))
            {
                continue;
            } 
            else 
            {
                if (array_key_exists($key, $this->related))
                {
                    if ($v instanceof RelationInterface) 
                    {
                        $v = $v->getRelated();
                    }
                }
                else
                {
                    $v = $options['raw'] ? $this->get($key) : $this->$key;
                }
                
                if ($v instanceof EntityInterface) 
                {
                    $v = $v->export([], [], $options);
                } 
                else if ($v instanceof Collection)
                {
                    $v = $this->exportCollection($v, $options);
                }
                
                $data[$key] = $v;
                $data['_class'] = $this->className;
            }
        }
        
        if ($options['format'] == self::FORMAT_JSON)
        {
            $data = json_encode($data);
        }
        
        return $data;
    }
    
    /**
     * @ignore
     */
    public function &__get($key) 
    {
        $value = parent::__get($key);

        // Property is a relationship
        if (array_key_exists($key, $this->related))
        {
            if (!$value->isFilled()) 
            {
                $value->load();
            }

            $value = $value->getRelated();
        }

        return $value;
    }
    
    /**
     * @ignore
     */
    public function __call($name, $arguments) 
    {
        if (array_key_exists($name, $this->related))
        {
            return $this->get($name);
        }
        
        throw new \BadMethodCallException(sprintf('The "%s" method does not exist.', $name));
    }
    
    /**
     * @ignore
     */
    public function __callStatic($name, $arguments) 
    {
        $self = new static();
        
        if ($name == 'withConnectionManager')
        {
            $self->setConnectionManager($arguments[0]);
            return $self->find();
        }

        return call_user_func_array([$self->find(), $name], $arguments);
    }
    
    /**
     * @ignore
     */
    public function __clone() 
    {
        foreach ((array)$this->primaryKey as $key) 
        {
            $this->set($key, $this->_ignoreValue);
        }

        $this->sync(self::SYNC_FILLABLE);
    }
}
