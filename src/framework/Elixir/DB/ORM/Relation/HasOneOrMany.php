<?php

namespace Elixir\DB\ORM\Relation;

use Elixir\DB\ORM\Collection;
use Elixir\DB\ORM\Relation\Pivot;
use Elixir\DB\ORM\Relation\RelationInterface;
use Elixir\DB\ORM\RepositoryInterface;
use Elixir\DB\ORM\Select;
use Elixir\DB\Query\SQL\JoinClause;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class HasOneOrMany implements RelationInterface
{
    /**
     * @var RepositoryInterface 
     */
    protected $_repository;
    
    /**
     * @var string|RepositoryInterface 
     */
    protected $_target;
    
    /**
     * @var string
     */
    protected $_type;

    /**
     * @var string 
     */
    protected $_foreignKey;
    
    /**
     * @var string 
     */
    protected $_otherKey;
    
    /**
     * @var Pivot 
     */
    protected $_pivot;
    
    /**
     * @var array 
     */
    protected $_criterions = [];
    
    /**
     * @var RepositoryInterface|Collection
     */
    protected $_related;
    
    /**
     * @var boolean
     */
    protected $_filled = false;

    /**
     * @param RepositoryInterface $pRepository
     * @param string|RepositoryInterface $pTarget
     * @param string $pForeignKey
     * @param string $pOtherKey
     * @param Pivot $pPivot
     * @param string $pType
     */
    public function __construct(RepositoryInterface $pRepository, 
                                $pTarget, 
                                $pForeignKey, 
                                $pOtherKey = null, 
                                Pivot $pPivot = null, 
                                $pType = self::HAS_ONE) 
    {
        $this->_repository = $pRepository;
        $this->_target = $pTarget;
        $this->_foreignKey = $pForeignKey;
        $this->_otherKey = $pOtherKey;
        $this->_pivot = $pPivot;
        $this->_type = $pType;
    }
    
    /**
     * @see RelationInterface::getType()
     */
    public function getType()
    {
        return $this->_type;
    }
    
    /**
     * @return RepositoryInterface
     */
    public function getRepository()
    {
        return $this->_repository;
    }
    
    /**
     * @return string|RepositoryInterface
     */
    public function getTarget()
    {
        return $this->_target;
    }
    
    /**
     * @return string
     */
    public function getForeignKey()
    {
        return $this->_foreignKey;
    }
    
    /**
     * @return string
     */
    public function getOtherKey()
    {
        return $this->_otherKey;
    }
    
    /**
     * @param Pivot $pPivot
     * @return HasOneOrMany
     */
    public function withPivot(Pivot $pPivot)
    {
        $this->_pivot = $pPivot;
        return $this;
    }
    
    /**
     * @return Pivot
     */
    public function getPivot()
    {
        return $this->_pivot;
    }
    
    /**
     * @param callable $pCriterion
     * @return RelationInterface
     */
    public function addCriterion(callable $pCriterion)
    {
        $this->_criterions[] = $pCriterion;
        $this->setFilled(false);
        
        return $this;
    }

    /**
     * @return array
     */
    public function getCriterions()
    {
        return $this->_criterions;
    }
    
    /**
     * @see RelationInterface::setRelated()
     */
    public function setRelated($pValue, $pFilled = true)
    {
        if(is_array($pValue))
        {
            $pValue = new Collection($pValue, true);
        }
        
        $this->_related = $pValue;
        $this->_filled = $pFilled;
    }
    
    /**
     * @see RelationInterface::getRelated()
     */
    public function getRelated()
    {
        return $this->_related;
    }
    
    /**
     * @see RelationInterface::setFilled()
     */
    public function setFilled($pValue)
    {
        $this->_filled = $pValue;
    }
    
    /**
     * @see RelationInterface::isFilled()
     */
    public function isFilled()
    {
        return $this->_filled;
    }

    /**
     * @see RelationInterface::load()
     */
    public function load()
    {
        if(!$this->_target instanceof RepositoryInterface)
        {
            $this->_target = new $this->_target();
            $this->_target->setConnectionManager($this->_repository->getConnectionManager());
        }
        
        $this->_otherKey = $this->_otherKey ?: $this->_target->getPrimaryKey();
        $select = $this->_target->select();
        
        if(false !== $this->performConstraints($select))
        {
            if(false !== $this->performCriterions($select))
            {
                $this->setRelated($this->match($select), true);
            }
            else
            {
                $this->setRelated(
                    $this->_type === self::HAS_ONE ? null : new Collection([], true),
                    true
                );
            }
        }
        else
        {
            $this->setRelated(
                $this->_type === self::HAS_ONE ? null : new Collection([], true),
                true
            );
        }
    }
    
    /**
     * @param Select $pSelect
     * @return boolean
     */
    protected function performConstraints(Select $pSelect)
    {
        if(null !== $this->_pivot)
        {
            $pSelect->join(
                $this->_pivot->getPivot(),
                function(JoinClause $pSQL)
                {
                    $pSQL->on(
                        sprintf(
                            '`%s`.`%s` = ?', 
                            $this->_pivot->getPivot(),
                            $this->_pivot->getForeignKey()
                        ),
                        $this->_repository->get($this->_foreignKey)
                    );

                    $pSQL->on(
                        sprintf(
                            '`%s`.`%s` = `%s`.`%s`', 
                            $this->_pivot->getPivot(),
                            $this->_pivot->getOtherKey(),
                            $this->target->getTable(),
                            $this->_otherKey
                        )
                    );

                    foreach($this->_pivot->getCriterions() as $criterion)
                    {
                        $criterion($pSQL);
                    }
                }
            );
        }
        else
        {
            $value = $this->_repository->get($this->_otherKey);

            if(null === $value)
            {
                return false;
            }

            $pSelect->where(
                sprintf(
                    '`%s`.`%s` = ?', 
                    $this->_target->getTable(),
                    $this->_foreignKey
                ),
                $value
            );
        }
        
        return true;
    }
    
    /**
     * @param Select $pSelect
     * @return boolean
     */
    protected function performCriterions(Select $pSelect)
    {
        foreach($this->_criterions as $criterion)
        {
            if(false === $criterion($pSelect, $this))
            {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * @param Select $pSelect
     * @return mixed
     */
    protected function match(Select $pSelect)
    {
        switch($this->_type)
        {
            case self::HAS_ONE:
                return $this->one($pSelect);
            break;
            case self::HAS_MANY:
                return $this->all($pSelect);
            break;
        }
        
        return null;
    }
    
    /**
     * @param Select $pSelect
     * @return RepositoryInterface
     */
    protected function one(Select $pSelect) 
    {
        return $pSelect->one();
    }
    
    /**
     * @param Select $pSelect
     * @return Collection
     */
    protected function all(Select $pSelect) 
    {
        return new Collection($pSelect->all(), true);
    }
}
