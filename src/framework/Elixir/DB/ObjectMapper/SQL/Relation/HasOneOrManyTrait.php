<?php

namespace Elixir\DB\ObjectMapper\SQL\Relation;

use Elixir\DB\ObjectMapper\Collection;
use Elixir\DB\ObjectMapper\CollectionEvent;
use Elixir\DB\ObjectMapper\FindableInterface;
use Elixir\DB\ObjectMapper\RelationInterface;
use Elixir\DB\ObjectMapper\RepositoryInterface;
use Elixir\DB\ObjectMapper\SQL\Relation\Pivot;
use Elixir\DB\Query\SQL\JoinClause;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
trait HasOneOrManyTrait 
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
    protected $otherKey;

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
     * @param Pivot $pivot
     * @return RelationInterface
     */
    public function withPivot(Pivot $pivot)
    {
        $this->pivot = $pPivot;
        return $this;
    }

    /**
     * @param callable $criteria
     * @return RelationInterface
     */
    public function addCriteria(callable $criteria)
    {
        $this->criterias[] = $criteria;
        return $this;
    }

    /**
     * @see RelationInterface::setRelated()
     */
    public function setRelated($value, $filled = true) 
    {
        if ($this->related instanceof Collection) 
        {
            $this->related->removeListener(CollectionEvent::VALUE_ADDED, [$this, 'onValueAdded']);
            $this->related->removeListener(CollectionEvent::VALUE_REMOVED, [$this, 'onValueRemoved']);
            $this->related = null;
        }

        if (is_array($value)) 
        {
            $value = new Collection($value, true);
        }

        $this->related = $value;

        if ($this->related instanceof Collection)
        {
            $this->related->setUseEvents(true);
            $this->related->addListener(CollectionEvent::VALUE_ADDED, [$this, 'onValueAdded']);
            $this->related->addListener(CollectionEvent::VALUE_REMOVED, [$this, 'onValueRemoved']);
        }

        $this->filled = $filled;
    }

    /**
     * @param CollectionEvent $e
     */
    public function onValueAdded(CollectionEvent $e)
    {
        // Todo
    }

    /**
     * @param CollectionEvent $e
     */
    public function onValueRemoved(CollectionEvent $e) 
    {
        // Todo
    }

    /**
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
        if (!$this->target instanceof RepositoryInterface) 
        {
            $class = $this->target;
            $this->target = $class::factory();
            $this->target->setConnectionManager($this->repository->getConnectionManager());
        }

        $findable = $this->target->find();

        if($this->prepareQuery($findable))
        {
            if($this->extendQuery($findable))
            {
                $this->setRelated($this->match($findable), true);
                return;
            }
        }

        $this->setRelated(
            $this->type === self::HAS_ONE ? null : new Collection([], true), 
            true
        );
    }

    /**
     * @param FindableInterface $findable
     * @return boolean
     */
    protected function prepareQuery(FindableInterface $findable) 
    {
        if (null !== $this->pivot) 
        {
            if (null === $this->pivot->getForeignKey()) 
            {
                $this->pivot->setForeignKey($this->target->getStockageName() . '_id');
            }

            if (null === $this->pivot->getOtherKey()) 
            {
                $this->pivot->setOtherKey($this->repository->getStockageName() . '_id');
            }

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
                        $this->repository->get($this->repository->getPrimaryKey())
                    );
                    
                    $join->on(
                        sprintf(
                            '`%s`.`%s` = `%s`.`%s`', 
                            $this->pivot->getPivot(), 
                            $this->pivot->getForeignKey(), 
                            $this->target->getStockageName(), 
                            $this->target->getPrimaryKey()
                        )
                    );

                    foreach ($this->pivot->getCriterias() as $criteria) 
                    {
                        call_user_func_array($criteria, [$join]);
                    }
                }
            );
        } 
        else 
        {
            $this->foreignKey = $this->foreignKey ?: $this->target->getStockageName() . '_id';
            $this->otherKey = $this->otherKey ?: $this->repository->getPrimaryKey();
        
            $value = $this->repository->get($this->otherKey);

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
        }

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
     * @param FindableInterface $select
     * @return mixed
     */
    protected function match(FindableInterface $findable)
    {
        switch ($this->type)
        {
            case self::HAS_ONE:
                return $findable->one();
            case self::HAS_MANY:
                return new Collection($findable->all(), true);
        }

        return null;
    }
}
