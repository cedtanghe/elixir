<?php

namespace Elixir\DB\ObjectMapper\SQL;

use Elixir\DB\ObjectMapper\EntityAbstract;
use Elixir\DB\ObjectMapper\EntityEvent;
use Elixir\DB\ObjectMapper\RepositoryEvent;
use Elixir\DB\ObjectMapper\SQL\RepositoryInterface;
use Elixir\DB\Query\SQL\SQLInterface;
use Elixir\DI\ContainerInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
abstract class RepositoryAbstract extends EntityAbstract implements RepositoryInterface 
{
    /**
     * @var string
     */
    const DEFAULT_CONNECTION_KEY = 'db.default';

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
     * @param ContainerInterface $manager
     * @param array $data
     */
    public function __construct(ContainerInterface $manager = null, array $data = []) 
    {
        $this->setConnectionManager($manager);

        $this->addListener(EntityEvent::CREATE_ENTITY, function(EntityEvent $e) 
        {
            $entity = $e->getEntity();
            $e->setEntity(new $entity($this->connectionManager));
        });

        if (null === $this->table) 
        {
            $this->table = lcfirst(pathinfo($this->className, PATHINFO_BASENAME));
        }

        parent::__construct($data);
    }

    /**
     * @see RepositoryInterface::setConnectionManager()
     */
    public function setConnectionManager(ContainerInterface $value) 
    {
        $this->connectionManager = $value;
    }

    /**
     * @see RepositoryInterface::getConnectionManager()
     */
    public function getConnectionManager() 
    {
        return $this->connectionManager;
    }

    /**
     * @see RepositoryInterface::getConnection()
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
     * @see RepositoryInterface::getPrimaryValue()
     */
    public function getPrimaryValue() 
    {
        if (null === $this->primaryKey) 
        {
            return null;
        }

        $result = [];

        foreach ((array)$this->primaryKey as $key)
        {
            $result[] = $this->$key;
        }

        return count($result) == 1 ? $result[0] : $result;
    }
    
    /**
     * @see RepositoryInterface::find()
     */
    public function find($options = null)
    {
        $findable = new Findable($this);
        return $findable;
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

        foreach ($this->_data as $key => $value)
        {
            $this->set($key, $this->_ignoreValue);
        }

        $this->sync(self::SYNC_FILLABLE);
        return $result;
    }
}
