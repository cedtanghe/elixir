<?php

namespace Elixir\DB\ORM;

use Elixir\DB\ORM\Collection;
use Elixir\DB\ORM\ModelEvent;
use Elixir\DB\ORM\Relation\BelongsTo;
use Elixir\DB\ORM\Relation\HasMany;
use Elixir\DB\ORM\Relation\HasOne;
use Elixir\DB\ORM\Relation\Pivot;
use Elixir\DB\ORM\Relation\Relation;
use Elixir\DB\ORM\Relation\RelationInterface;
use Elixir\DB\ORM\RepositoryInterface;
use Elixir\DB\ORM\Select;
use Elixir\DB\SQL\Expr;
use Elixir\DB\SQL\Insert;
use Elixir\DB\SQL\Update;
use Elixir\DI\ContainerInterface;
use Elixir\Dispatcher\Dispatcher;
use Elixir\Util\Str;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

abstract class ModelAbstract extends Dispatcher implements RepositoryInterface
{
    /**
     * @var string
     */
    const READ_ONLY = 'read_only';
    
    /**
     * @var string
     */
    const FILLABLE = 'fillable';
    
    /**
     * @var string
     */
    const GUARDED = 'guarded';
    
    /**
     * @var null
     */
    const IGNORE_VALUE = null;
    
    /**
     * @var string
     */
    const DEFAULT_CONNECTION_KEY = 'db.default';
    
    /**
     * @param ContainerInterface $pConnectionManager
     * @param array $pData
     * @return ModelAbstract
     */
    public static function create(ContainerInterface $pManager = null, array $pData = [])
    {
        return new static($pManager, $pData);
    }

    /**
     * @param boolean $pWithPrefix
     * @param string $pAlias
     * @param string $pReference
     * @return array
     */
    public static function columns($pWithPrefix = true, $pAlias = null, $pReference = null)
    {
        $model = new static();
        $keys = $model->getColumnKeys();
        $columns = [];
        
        foreach($keys as $column)
        {
            $c = sprintf('`%s`.`%s`', $pAlias ?: $model->getTable(), $column);
            
            if($pWithPrefix)
            {
                $c .= sprintf(
                    ' as `%s%s::%s`', 
                    $pReference ? $pReference . '::' : '',
                    $model->getClassName(),
                    $column
                );
            }
            
            $columns[] = $c;
        }
        
        return $columns;
    }
    
    /**
     * @param mixed $pValue
     * @return boolean
     */
    public static function isCollection($pValue)
    {
        return Collection::isCollection($pValue);
    }
    
    /**
     * @param mixed $pValue
     * @return mixed
     */
    public static function convertIfCollection($pValue)
    {
        if(static::isCollection($pValue))
        {
            return $pValue->getArrayCopy();
        }
        
        return $pValue;
    }
    
    /**
     * @var array
     */
    protected static $_mutatorsGet = [];

    /**
     * @var array
     */
    protected static $_mutatorsSet = [];
    
    /**
     * @var ContainerInterface
     */
    protected $_connectionManager;
    
    /**
     * @var string
     */
    protected $_className;
    
    /**
     * @var string
     */
    protected $_table;
    
    /**
     * @var mixed
     */
    protected $_primaryKey = 'id';
    
    /**
     * @var mixed
     */
    protected $_ignoreValue = self::IGNORE_VALUE;
    
    /**
     * @var boolean
     */
    protected $_autoIncrement = true;
    
    /**
     * @var array
     */
    protected $_fillable = [];
    
    /**
     * @var array
     */
    protected $_original = [];
    
    /**
     * @var array
     */
    protected $_guarded = [];
    
    /**
     * @var array
     */
    protected $_related = [];
    
    /**
     * @var array
     */
    protected $_data = [];
    
    /**
     * @var array
     */
    protected $_filled = [];
    
    /**
     * @var string
     */
    protected $_state = self::FILLABLE;
    
    /**
     * @param ContainerInterface $pConnectionManager
     * @param array $pData
     * @throws \LogicException
     */
    public function __construct(ContainerInterface $pManager = null, array $pData = []) 
    {
        $this->_connectionManager = $pManager;
        $this->_className = get_class($this);
        
        $this->_state = self::FILLABLE;
        $this->defineColumns();
        $this->dispatch(new ModelEvent(ModelEvent::DEFINE_COLUMNS));
        
        $this->_state = self::GUARDED;
        $this->defineGuarded();
        $this->dispatch(new ModelEvent(ModelEvent::DEFINE_GUARDED));
        
        $this->unfilledIfIsIgnoreValue();
        
        if(null === $this->_table)
        {
            $this->_table = lcfirst(pathinfo($this->_className, PATHINFO_BASENAME));
        }
        
        if(null !== $this->_primaryKey)
        {
            $keys = array_keys($this->_data);
            
            foreach((array)$this->_primaryKey as $primary)
            {
                if(!in_array($primary, $keys))
                {
                    throw new \LogicException(sprintf('Primary key "%s" for the model "%s" are not valid.', $primary, $this->_className));
                }
            }
        }
        
        if(!empty($pData))
        {
            $this->hydrate($pData, ['raw' => true, 'sync' => true]);
        }
    }
    
    /**
     * Declares columns
     */
    abstract protected function defineColumns();
    
    /**
     * Declares relations and others
     */
    protected function defineGuarded(){}
    
    /**
     * @see RepositoryInterface::setConnectionManager()
     */
    public function setConnectionManager(ContainerInterface $pValue)
    {
        $this->_connectionManager = $pValue;
    }
    
    /**
     * @see RepositoryInterface::getConnectionManager()
     */
    public function getConnectionManager()
    {
        return $this->_connectionManager;
    }
    
    /**
     * @see RepositoryInterface::getConnection()
     */
    public function getConnection($pKey = null)
    {
        if(null !== $pKey)
        {
            if($this->_connectionManager->has($pKey))
            {
                return $this->_connectionManager->get($pKey);
            }
        }
        
        return $this->_connectionManager->get(self::DEFAULT_CONNECTION_KEY);
    }
    
    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->_className;
    }
    
    /**
     * @see RepositoryInterface::getTable()
     */
    public function getTable()
    {
        return $this->_table;
    }
    
    /**
     * @see RepositoryInterface::getPrimaryKey()
     */
    public function getPrimaryKey()
    {
        return $this->_primaryKey;
    }
    
    /**
     * @see RepositoryInterface::getPrimaryValue()
     */
    public function getPrimaryValue()
    {
        $primary = $this->getPrimaryValue;
        
        if(null === $this->_primaryKey)
        {
            return null;
        }
        
        $result = [];
        
        foreach((array)$this->_primaryKey as $primary)
        {
            $result[] = $this->$primary;
        }
        
        return count($result) == 1 ? $result[0] : $result;
    }
    
    /**
     * @return mixed
     */
    public function getIgnoreValue()
    {
        return $this->_ignoreValue;
    }
    
    /**
     * @return boolean
     */
    public function isAutoIncrement()
    {
        return $this->_autoIncrement;
    }

    /**
     * @return array
     */
    public function getColumnKeys()
    {
        return $this->_fillable;
    }
    
    /**
     * @return array
     */
    public function getGuardedKeys()
    {
        return $this->_guarded;
    }
    
    /**
     * @return array
     */
    public function getRelatedKeys()
    {
        return array_keys($this->_related);
    }
    
    /**
     * @return string
     */
    public function getRelatedType($pKey)
    {
        return isset($this->_related[$pKey]) ? $this->_related[$pKey] : null;
    }
    
    /**
     * @param string $pValue
     */
    public function setState($pValue)
    {
        $this->_state = $pValue;
    }
    
    /**
     * @return string
     */
    public function getState()
    {
        return $this->_state;
    }
    
    /**
     * @return boolean
     */
    public function isReadOnly()
    {
        return $this->_state == self::READ_ONLY;
    }
    
    /**
     * @return boolean
     */
    public function isFillable()
    {
        return $this->_state == self::FILLABLE;
    }
    
    /**
     * @return boolean
     */
    public function isGuarded()
    {
        return $this->_state == self::GUARDED;
    }
    
    /**
     * @param callable $pCallback
     * @return Relation
     */
    protected function relation(callable $pCallback)
    {
        return new Relation($pCallback);
    }
    
    /**
     * @param string|ModelAbstract $pTarget
     * @param string $pForeignKey
     * @param string $pOtherKey
     * @param Pivot $pPivot
     * @return HasOne
     */
    protected function hasOne($pTarget, $pForeignKey, $pOtherKey = null, Pivot $pPivot = null)
    {
        return new HasOne(
            $this, 
            $pTarget, 
            $pForeignKey, 
            $pOtherKey,
            $pPivot
        );
    }
    
    /**
     * @param string|ModelAbstract $pTarget
     * @param string $pForeignKey
     * @param string $pOtherKey
     * @param Pivot $pPivot
     * @return HasMany
     */
    protected function hasMany($pTarget, $pForeignKey, $pOtherKey = null, Pivot $pPivot = null)
    {
        return new HasMany(
            $this, 
            $pTarget, 
            $pForeignKey, 
            $pOtherKey,
            $pPivot
        );
    }
    
    /**
     * @param string|ModelAbstract $pTarget
     * @param string $pForeignKey
     * @param string $pOtherKey
     * @param Pivot $pPivot
     * @return BelongsTo
     */
    protected function belongsTo($pTarget, $pForeignKey, $pOtherKey = null, Pivot $pPivot = null)
    {
        return new BelongsTo(
            $this, 
            $pTarget, 
            $pForeignKey, 
            $pOtherKey,
            $pPivot
        );
    }
    
    /**
     * @param string $pPivot
     * @param string $pForeignKey
     * @param string $pOtherKey
     * @return Pivot
     */
    protected function pivot($pPivot, $pForeignKey, $pOtherKey)
    {
        return new Pivot(
            $pPivot, 
            $pForeignKey, 
            $pOtherKey
        );
    }
    
    /**
     * @return boolean
     */
    public function exist()
    {
        if(null === $this->_primaryKey)
        {
            return false;
        }
        
        foreach((array)$this->_primaryKey as $primary)
        {
            if(null === $this->$primary || $this->_ignoreValue === $this->$primary)
            {
                return false;
            }
        }
        
        return true;
    }

    /**
     * @param string $pKey
     * @return boolean
     */
    public function isFilled($pKey)
    {
        if(isset($this->_filled[$pKey]))
        {
            return $this->_filled[$pKey];
        }
        
        return false;
    }
    
    public function unfilledIfIsIgnoreValue()
    {
        foreach($this->_filled as $key => &$value)
        {
            $raw = $this->get($key);
            
            if(null === $raw || $this->_ignoreValue === $raw)
            {
                $value = false;
            }
        }
    }

    /**
     * @see RepositoryInterface::has()
     */
    public function has($pKey)
    {
        return array_key_exists($pKey, $this->_data);
    }
    
    /**
     * @see RepositoryInterface::set()
     * @throws \InvalidArgumentException
     */
    public function set($pKey, $pValue, $pFilled = true)
    {
        if($this->isReadOnly())
        {
            if(!$this->has($pKey))
            {
                throw new \InvalidArgumentException(sprintf('Key "%s" is a not declared property.', $pKey));
            }
        }
        
        if(is_array($pValue))
        {
            $pValue = new Collection($pValue, true);
        }
        
        if($this->isFillable())
        {
            if(!in_array($pKey, $this->_fillable))
            {
                $this->_fillable[] = $pKey;
            }
        }
        else if($this->isGuarded())
        {
            if(!in_array($pKey, $this->_guarded))
            {
                $this->_guarded[] = $pKey;
            }
            
            if(array_key_exists($pKey, $this->_related))
            {
                $relation = $this->get($pKey);
                $relation->setRelated($pValue, $pFilled);
                
                $pValue = $relation;
            }
            else if($pValue instanceof RelationInterface)
            {
                $this->_related[$pKey] = $pValue->getType();
            }
        }
        
        $this->_data[$pKey] = $pValue;
        $this->_filled[$pKey] = $pFilled;
    }

    /**
     * @see RepositoryInterface::get()
     */
    public function get($pKey)
    {
        if(!$this->has($pKey))
        {
            return null;
        }
        
        return $this->_data[$pKey];
    }
    
    /**
     * @return boolean
     */
    protected function isModified()
    {
        return count($this->getModified()) > 0;
    }

    protected function getModified()
    {
        $modified = [];

        foreach($this->_fillable as $column)
        {
            $value = $this->get($column);
            
            if($value instanceof Expr)
            {
                $value = $value->getExpr();
            }
            
            if(!array_key_exists($column, $this->_original) || $this->_original[$column] != $value)
            {
                $modified[$column] = $value;
            }
        }

        return $modified;
    }
    
    public function sync()
    {
        foreach($this->_fillable as $column)
        {
            $value = $this->get($column);
            
            if($value instanceof Expr)
            {
                $value = $value->getExpr();
            }
        
            $this->_original[$column] = $value;
        }
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
        if($this->isReadOnly())
        {
            throw new \LogicException('It is impossible to create a read-only model.');
        }
        
        $this->dispatch(new ModelEvent(ModelEvent::PRE_INSERT));
        
        $DB = $this->getConnection('db.write');
        $data = [];

        foreach($this->_fillable as $column)
        {
            $data[$column] = $this->get($column);
        }
        
        $values = [];
        
        foreach($data as $key => $value)
        {
            if($this->_ignoreValue !== $value)
            {
                $values['`' . $key . '`'] = $value;
            }
        }
        
        $SQL = $DB->createInsert('`' . $this->_table . '`');
        $SQL->values($values, Insert::VALUES_SET);
        
        $result = $DB->exec($SQL);
        $result = $result > 0;
        
        if($result)
        {
            if($this->_autoIncrement && null !== $this->_primaryKey)
            {
                if(is_array($this->_primaryKey))
                {
                    throw new \LogicException('It is impossible to increment several primary keys.');
                }
                
                $this->{$this->_primaryKey} = $DB->lastInsertId();
            }
        }
        
        $this->dispatch(new ModelEvent(ModelEvent::INSERT));
        $this->sync();
        
        return $result;
    }
    
    /**
     * @see RepositoryInterface::update()
     * @throws \LogicException
     */
    public function update(array $pMembers = [], array $pOmitMembers = [])
    {
        if($this->isReadOnly())
        {
            throw new \LogicException('It is impossible to update a read-only model.');
        }
        
        $this->dispatch(new ModelEvent(ModelEvent::PRE_UPDATE));
        
        if(!$this->isModified())
        {
            $this->dispatch(new ModelEvent(ModelEvent::UPDATE));
            return true;
        }
        
        $DB = $this->getConnection('db.write');
        $data = [];

        foreach(array_keys($this->getModified()) as $column)
        {
            if(in_array($column, $pOmitMembers))
            {
                continue;
            }
            else if(count($pMembers) > 0 && !in_array($column, $pMembers))
            {
                continue;
            }
            
            $data[$column] = $this->get($column);
        }
        
        $values = [];
        
        foreach($data as $key => $value)
        {
            if($this->_ignoreValue !== $value)
            {
                $values['`' . $key . '`'] = $value;
            }
        }
        
        if(count($values) == 0)
        {
            $this->dispatch(new ModelEvent(ModelEvent::UPDATE));
            return true;
        }
        
        $SQL = $DB->createUpdate('`' . $this->_table . '`');
        $SQL->set($values, Update::VALUES_SET);
        
        if(null === $this->_primaryKey)
        {
            throw new \LogicException('No primary key is defined');
        }
        
        foreach((array)$this->_primaryKey as $primary)
        {
            $SQL->where(sprintf('`%s`.`%s` = ?', $this->_table, $primary), $this->get($primary));
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
    public function delete()
    {
        if($this->isReadOnly())
        {
            throw new \LogicException('It is impossible to delete a read-only model.');
        }
        
        $this->dispatch(new ModelEvent(ModelEvent::PRE_DELETE));
        
        $DB = $this->getConnection('db.write');
        $SQL = $DB->createDelete('`' . $this->_table . '`');

        if(null === $this->_primaryKey)
        {
            throw new \LogicException('No primary key is defined.');
        }
        
        foreach((array)$this->_primaryKey as $primary)
        {
            $SQL->where(sprintf('`%s`.`%s` = ?', $this->_table, $primary), $this->get($primary));
        }
        
        $result = $DB->exec($SQL);
        $result = $result > 0;
        
        $this->dispatch(new ModelEvent(ModelEvent::DELETE));
        
        foreach($this->_data as $key => $value)
        {
            $this->set($key, $this->_ignoreValue, false);
        }

        $this->sync();
        return $result;
    }
    
    /**
     * @see RepositoryInterface::select()
     */
    public function select($pAlias = null)
    {
        return new Select($this, $pAlias);
    }
    
    /**
     * @see RepositoryInterface::hydrate()
     */
    public function hydrate(array $pData, array $pOptions = ['raw' => true])
    {
        $references = [];
        $models = [];
        
        foreach($pData as $key => $value)
        {
            if(false !== strpos($key, '::'))
            {
                $reference = null;
                $segments = explode('::', $key);
                
                if(count($segments) == 3)
                {
                    $reference = array_shift($segments);
                    $class = $segments[0];
                }
                else
                {
                    if($segments[0] != $this->_className)
                    {
                        $class = $segments[0];

                        if(!isset($references[$class]))
                        {
                            $reference = lcfirst(pathinfo($class, PATHINFO_BASENAME));
                            $references[$class] = $reference;
                        }
                        else
                        {
                            $reference = $references[$class];
                        }
                    }
                }
                
                if(null !== $reference)
                {
                    if(!isset($models[$reference]))
                    {
                        $models[$reference] = ['class' => $class, 'value' => []];
                    }
                    
                    $models[$reference]['value'][$segments[1]] = $value;
                    continue;
                }
            }
            
            if(static::isCollection($value) || is_array($value))
            {
                $value = $this->hydrateCollection($value, $pOptions);
            }

            if(isset($pOptions['raw']) && $pOptions['raw'])
            {
                $this->set($key, $value, true);
            }
            else
            {
                $this->$key = $value;
            }
        }
        
        if(count($models) > 0)
        {
            foreach($models as $key => $value)
            {
                $model = new $value['class']();
                $model->setConnectionManager($this->_connectionManager);
                $model->hydrate($value['value'], $pOptions);
                
                if(isset($pOptions['raw']) && $pOptions['raw'])
                {
                    $this->set($key, $model, true);
                }
                else
                {
                    $this->$key = $model;
                }
            }
        }
        
        if(isset($pOptions['sync']) && $pOptions['sync'])
        {
            $this->sync();
        }
    }
    
    /**
     * @param array|Collection $pData
     * @param array $pOptions
     * @return array
     */
    protected function hydrateCollection(array $pData, $pOptions)
    {
        if(isset($pData['_class']))
        {
             $model = new $pData['_class']();
             $model->setConnectionManager($this->_connectionManager);
             $model->hydrate($pData, $pOptions);

             $pData = $model;
        }
        else
        {
            foreach($pData as $key => &$value)
            {
                if(static::isCollection($value) || is_array($value))
                {
                    $value = $this->hydrateCollection($value, $pOptions);
                }
            }
        }
        
        return $pData;
    }
    
    /**
     * @see RepositoryInterface::export()
     */
    public function export(array $pMembers = [], array $pOmitMembers = [], $pRaw = true)
    {
        $data = [];
        
        foreach(array_keys($this->_data) as $value)
        {
            if(in_array($value, $pOmitMembers))
            {
                continue;
            }
            else if(count($pMembers) > 0 && !in_array($value, $pMembers))
            {
                continue;
            }
            else
            {
                $v = $pRaw ? $this->get($value) : $this->$value;
                
                if(array_key_exists($value, $this->_related))
                {
                    if($v instanceof RelationInterface)
                    {
                        $v = $v->getRelated();
                    }
                }
                
                if($v instanceof self)
                {
                    $v = $v->export([], [], $pRaw);
                }
                else if(static::isCollection($v))
                {
                    $v = $this->exportCollection($v->getArrayCopy(), $pRaw);
                }
                
                $data[$value] = $v;
                $data['_class'] = $this->_className;
            }
        }
        
        return $data;
    }
    
    /**
     * @param array $pData
     * @param boolean $pRaw
     * @return array
     */
    protected function exportCollection(array $pData, $pRaw)
    {
        foreach($pData as $key => &$value)
        {
            if(static::isCollection($value))
            {
                $value = $this->exportCollection($value->getArrayCopy(), $pRaw);
            }
            else if($value instanceof self)
            {
                $value = $value->export([], [], $pRaw);
            }
        }
        
        return $pData;
    }

    /**
     * @param string $pKey
     * @return boolean
     */
    public function __isset($pKey)
    {
        return $this->has($pKey);
    }
    
    /**
     * @param string $pKey
     * @return mixed
     */
    public function __get($pKey)
    {
        $className = $this->getClassName();
        
        if(isset(static::$_mutatorsGet[$className][$pKey]))
        {
            if(false !== static::$_mutatorsGet[$className][$pKey])
            {
                return $this->{static::$_mutatorsGet[$className][$pKey]}();
            }
        }
        else
        {
            $method = 'get' . Str::camelize($pKey);

            if(method_exists($this, $method))
            {
                static::$_mutatorsGet[$className][$pKey] = $method;
                return $this->{$method}();
            }
            else
            {
                static::$_mutatorsGet[$className][$pKey] = false;
            }
        }
        
        $value = $this->get($pKey);
        
        // Property is a relationship
        if(array_key_exists($pKey, $this->_related))
        {
            if(!$value->isFilled())
            {
                $value->load();
            }
            
            $value = $value->getRelated();
        }
        
        return $value;
    }
    
    /**
     * @param string $pKey
     * @param mixed $pValue
     */
    public function __set($pKey, $pValue)
    {
        $className = $this->getClassName();
        
        if(isset(static::$_mutatorsSet[$className][$pKey]))
        {
            if(false !== static::$_mutatorsSet[$className][$pKey])
            {
                $this->{static::$_mutatorsSet[$className][$pKey]}($pValue);
                return;
            }
        }
        else
        {
            $method = 'set' . Str::camelize($pKey);

            if(method_exists($this, $method))
            {
                static::$_mutatorsSet[$className][$pKey] = $method;
                $this->{$method}($pValue);
                
                return;
            }
            else
            {
                static::$_mutatorsSet[$className][$pKey] = false;
            }
        }

        $this->set($pKey, $pValue, true);
    }
    
    /**
     * @param string $pKey
     */
    public function __unset($pKey) 
    {
        $this->set($pKey, $this->_ignoreValue, false);
    }
    
    /**
     * @return string
     */
    public function __toString() 
    {
        return $this->export();
    }
}
