<?php

namespace Elixir\DB\ObjectMapper\SQL\Relation;

use Elixir\DB\ObjectMapper\Collection;
use Elixir\DB\ObjectMapper\FindableInterface;
use Elixir\DB\ObjectMapper\RelationInterface;
use Elixir\DB\ObjectMapper\RelationInterfaceMetas;
use Elixir\DB\ObjectMapper\RepositoryInterface;
use Elixir\DB\ObjectMapper\SQL\Relation\Pivot;
use Elixir\DB\Query\SQL\JoinClause;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
abstract class BaseAbstract implements RelationInterfaceMetas
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var RepositoryInterface 
     */
    protected $repository;

    /**
     * @var string|RepositoryInterface 
     */
    protected $target;

    /**
     * @var string 
     */
    protected $foreignKey;

    /**
     * @var string 
     */
    protected $localKey;

    /**
     * @var Pivot 
     */
    protected $pivot;

    /**
     * @var array 
     */
    protected $criterias = [];

    /**
     * @var mixed
     */
    protected $related;

    /**
     * @var boolean
     */
    protected $filled = false;

    /**
     * @see RelationInterface::getType()
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return RepositoryInterface
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return RepositoryInterface
     */
    public function getTarget() 
    {
        if (!$this->target instanceof RepositoryInterface) 
        {
            $class = $this->target;
            $this->target = $class::factory();
            $this->target->setConnectionManager($this->repository->getConnectionManager());
        }

        return $this->target;
    }

    /**
     * @param string $value
     * @return BaseAbstract
     */
    public function setForeignKey($value)
    {
        $this->foreignKey = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getForeignKey()
    {
        if (null === $this->foreignKey) 
        {
            // Define target
            $this->getTarget();

            if ($this->pivot) 
            {
                $this->foreignKey = $this->target->getPrimaryKey();
            } 
            else 
            {
                $this->foreignKey = $this->target->getStockageName() . '_id';
            }
        }

        return $this->foreignKey;
    }

    /**
     * @param string $value
     * @return BaseAbstract
     */
    public function setLocalKey($value)
    {
        $this->localKey = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocalKey() 
    {
        if (null === $this->localKey)
        {
            $this->localKey = $this->repository->getPrimaryKey();
        }

        return $this->localKey;
    }

    /**
     * @param Pivot $pivot
     * @return BaseAbstract
     */
    public function withPivot(Pivot $pivot)
    {
        $this->pivot = $pPivot;
        return $this;
    }

    /**
     * @return Pivot
     */
    public function getPivot() 
    {
        if (null !== $this->pivot) 
        {
            // Define target
            $this->getTarget();

            if (null === $this->pivot->getForeignKey()) 
            {
                $this->pivot->setForeignKey($this->target->getStockageName() . '_id');
            }
            
            if (null === $this->pivot->getOtherKey())
            {
                $this->pivot->setOtherKey($this->target->getStockageName() . '_id');
            }
        }

        return $this->pivot;
    }

    /**
     * @param callable $criteria
     * @return BaseAbstract
     */
    public function addCriteria(callable $criteria)
    {
        $this->criterias[] = $criteria;
        return $this;
    }

    /**
     * @return array
     */
    public function getCriterias()
    {
        return $this->criterias;
    }

    /**
     * @see RelationInterface::setRelated()
     */
    public function setRelated($value, array $options = [])
    {
        $options = array_merge(
<<<<<<< HEAD
            ['filled' => true], 
            $options
        );
        
=======
            [
                'filled' => true,
                'internal' => false
            ],
            $options
        );
        
        if (null !== $this->related)
        {
            if ($this->related instanceof Collection)
            {
                $this->related->removeListener(CollectionEvent::VALUE_ADDED, [$this, 'onValueAdded']);
                $this->related->removeListener(CollectionEvent::VALUE_REMOVED, [$this, 'onValueRemoved']);

                if(!$options['internal'])
                {
                    foreach ($this->related as $object)
                    {
                        $this->dissociate($object);
                    }
                }
            }
            else
            {
                if(!$options['internal'])
                {
                    $this->dissociate($this->related);
                }
            }
            
            $this->related = null;
        }

>>>>>>> 920b6b5c12e07720d915cd8cca92ef0ba7863c3b
        if (is_array($value))
        {
            $value = new Collection($value);
        }

        $this->related = $value;
<<<<<<< HEAD
=======

        if ($this->related instanceof Collection)
        {
            $this->related->setUseEvents(true);
            $this->related->addListener(CollectionEvent::VALUE_ADDED, [$this, 'onValueAdded']);
            $this->related->addListener(CollectionEvent::VALUE_REMOVED, [$this, 'onValueRemoved']);
            
            if(!$options['internal'])
            {
                foreach ($this->related as $object)
                {
                    $this->associate($object);
                }
            }
        }
        else
        {
            if(!$options['internal'])
            {
                $this->associate($this->related);
            }
        }

>>>>>>> 920b6b5c12e07720d915cd8cca92ef0ba7863c3b
        $this->filled = $options['filled'];
    }

    /**
<<<<<<< HEAD
=======
     * @param CollectionEvent $e
     */
    public function onValueAdded(CollectionEvent $e)
    {
        $object = $e->getObject();
        
        if ($object instanceof RepositoryInterface)
        {
            $this->associate($object);
        }
    }
    
    /**
     * @param CollectionEvent $e
     */
    public function onValueRemoved(CollectionEvent $e)
    {
        $object = $e->getObject();
        
        if ($object instanceof RepositoryInterface)
        {
            $this->dissociate($object);
        }
    }
    
    /**
     * @param RepositoryInterface $target
     */
    abstract public function associate(RepositoryInterface $target);
    
    /**
     * @param RepositoryInterface $target
     */
    abstract public function dissociate(RepositoryInterface $target);

    /**
>>>>>>> 920b6b5c12e07720d915cd8cca92ef0ba7863c3b
     * @see RelationInterface::getRelated()
     */
    public function getRelated()
    {
        return $this->related;
    }

    /**
     * @see RelationInterface::isFilled()
     */
    public function isFilled()
    {
        return $this->filled;
    }

    /**
     * @see RelationInterface::load()
     */
    public function load()
    {
        // Define keys, pivot and target
        $this->getTarget();
        $this->getForeignKey();
        $this->getLocalKey();
        $this->getPivot();

        $findable = $this->target->find();

        if ($this->prepareQuery($findable))
        {
            if ($this->extendQuery($findable))
            {
<<<<<<< HEAD
                $this->setRelated($this->match($findable), ['filled' => true]);
=======
                $this->setRelated(
                    $this->match($findable), 
                    [
                        'filled' => true, 
                        'internal' => true
                    ]
                );
                
>>>>>>> 920b6b5c12e07720d915cd8cca92ef0ba7863c3b
                return;
            }
        }

        switch ($this->type) 
        {
            case self::HAS_ONE:
            case self::BELONGS_TO:
<<<<<<< HEAD
                $this->setRelated(null, ['filled' => true]);
                break;
            case self::HAS_MANY:
            case self::BELONGS_TO_MANY:
                $this->setRelated(new Collection([]), ['filled' => true]);
=======
                $this->setRelated(
                    null,
                    [
                        'filled' => true, 
                        'internal' => true
                    ]
                );
                
                break;
            case self::HAS_MANY:
            case self::BELONGS_TO_MANY:
                $this->setRelated(
                    new Collection([], true), 
                    [
                        'filled' => true, 
                        'internal' => true
                    ]
                );
                
>>>>>>> 920b6b5c12e07720d915cd8cca92ef0ba7863c3b
                break;
        }
    }

    /**
     * @param FindableInterface $findable
     * @return boolean
     */
    protected function prepareQuery(FindableInterface $findable)
    {
        return null !== $this->pivot ? $this->parsePivot($findable) : $this->parseQuery($findable);
    }

    /**
     * @param FindableInterface $findable
     * @return boolean
     */
    protected function parsePivot(FindableInterface $findable) 
    {
        $findable->innerJoin(
            $this->pivot->getPivot(), 
            function(JoinClause $join)
            {
                $join->on(
                    sprintf(
                        '`%s`.`%s` = ?', 
                        $this->pivot->getPivot(), 
                        $this->pivot->getOtherKey()
                    ), 
                    $this->repository->get($this->localKey)
                );

                $join->on(
                    sprintf(
                        '`%s`.`%s` = `%s`.`%s`', 
                        $this->pivot->getPivot(), 
                        $this->pivot->getForeignKey(), 
                        $this->target->getStockageName(), 
                        $this->foreignKey
                    )
                );

                foreach ($this->pivot->getCriterias() as $criteria)
                {
                    call_user_func_array($criteria, [$join]);
                }
            }
        );

        return true;
    }

    /**
     * @param FindableInterface $findable
     * @return boolean
     */
    protected function parseQuery(FindableInterface $findable)
    {
        $value = $this->repository->get($this->localKey);

        if (null === $value)
        {
            return false;
        }

        $findable->where(
            sprintf(
                '`%s`.`%s` = ?', 
                $this->target->getStockageName(), 
                $this->foreignKey
            ), 
            $value
        );

        return true;
    }

    /**
     * @param FindableInterface $findable
     * @return boolean
     */
    protected function extendQuery(FindableInterface $findable)
    {
        foreach ($this->criterias as $criteria) 
        {
            if (false === call_user_func_array($criteria, [$findable, $this]))
            {
                return false;
            }
        }

        return true;
    }

    /**
     * @param FindableInterface $findable
     * @return mixed
     */
    protected function match(FindableInterface $findable) 
    {
        switch ($this->type) 
        {
            case self::HAS_ONE:
            case self::BELONGS_TO:
                return $findable->one();
            case self::HAS_MANY:
            case self::BELONGS_TO_MANY:
                return new Collection($findable->all());
        }

        return null;
    }
}
