<?php

namespace Elixir\DB\ORM;

use Elixir\DB\ORM\EntityAbstract;
use Elixir\DB\ORM\EntityEvent;
use Elixir\DB\ORM\RepositoryInterface;
use Elixir\DB\Query\SQL\SQLInterface;
use Elixir\DI\ContainerInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
abstract class SQLRepositoryAbstract extends EntityAbstract implements RepositoryInterface
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
    public function getConnection($key = null)
    {
        if (null !== $key) 
        {
            if ($this->connectionManager->has($key)) 
            {
                return $this->connectionManager->get($key);
            }
        }

        return $this->connectionManager->get(self::DEFAULT_CONNECTION_KEY);
    }

    /**
     * @see RepositoryInterface::getTable()
     */
    public function getTable() 
    {
        return $this->table;
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
     * @return boolean
     */
    public function isAutoIncrement() 
    {
        return $this->autoIncrement;
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
            if (null === $this->$key || $this->ignoreValue === $this->$key)
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

        $SQL = $DB->createInsert('`' . $this->table . '`');
        $SQL->values($values, SQLInterface::VALUES_SET);

        // Todo event to parse query
        
        $result = $DB->exec($SQL);
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

        $SQL = $DB->createUpdate('`' . $this->table . '`');
        $SQL->set($values, Update::VALUES_SET);

        if (null === $this->primaryKey) {
            throw new \LogicException('No primary key is defined');
        }

        foreach ((array) $this->primaryKey as $primary) {
            $SQL->where(sprintf('`%s`.`%s` = ?', $this->table, $primary), $this->get($primary));
        }

        $result = $DB->exec($SQL);
        $result = $result > 0;

        $this->dispatch(new ModelEvent(ModelEvent::UPDATE));
        $this->sync();

        return $result;
    }

    /**
     * @see RepositoryInterface::delete()
     * @throws \LogicException
     */
    public function delete() {
        if ($this->isReadOnly()) {
            throw new \LogicException('It is impossible to delete a read-only model.');
        }

        $this->dispatch(new ModelEvent(ModelEvent::PRE_DELETE));

        $DB = $this->getConnection('db.write');
        $SQL = $DB->createDelete('`' . $this->table . '`');

        if (null === $this->primaryKey) {
            throw new \LogicException('No primary key is defined.');
        }

        foreach ((array) $this->primaryKey as $primary) {
            $SQL->where(sprintf('`%s`.`%s` = ?', $this->table, $primary), $this->get($primary));
        }

        $result = $DB->exec($SQL);
        $result = $result > 0;

        $this->dispatch(new ModelEvent(ModelEvent::DELETE));

        foreach ($this->_data as $key => $value) {
            $this->set($key, $this->_ignoreValue, false);
        }

        $this->sync();
        return $result;
    }

}
