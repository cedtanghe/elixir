<?php

namespace Elixir\DB\ORM\Relation;

use Elixir\DB\ORM\Collection;
use Elixir\DB\ORM\Relation\Pivot;
use Elixir\DB\ORM\Relation\RelationInterface;
use Elixir\DB\ORM\RepositoryInterface;
use Elixir\DB\ORM\Select;

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
    protected $_criterions = array();
    
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
     * @throws \InvalidArgumentException
     */
    public function addCriterion($pCriterion)
    {
        if(!is_callable($pCriterion))
        {
            throw new \InvalidArgumentException('Criterion argument must be a callable.');
        }
        
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
        
        $select = $this->_target->select();
        
        if(false !== $this->eagerConstraints($select))
        {
            if(false !== $this->eagerCriterions($select))
            {
                $this->setRelated($this->match($select), true);
            }
            else
            {
                $this->setRelated(
                    $this->_type === self::HAS_ONE ? null : new Collection(array(), true),
                    true
                );
            }
        }
        else
        {
            $this->setRelated(
                $this->_type === self::HAS_ONE ? null : new Collection(array(), true),
                true
            );
        }
    }
    
    /**
     * @param Select $pSelect
     * @return boolean
     */
    protected function eagerConstraints(Select $pSelect)
    {
        if(null !== $this->_pivot)
        {
            $this->_pivot->join($this, $pSelect);
        }
        else
        {
            $value = $this->_repository->get($this->_otherKey ?: $this->_repository->getPrimaryKey());

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
    protected function eagerCriterions(Select $pSelect)
    {
        foreach($this->_criterions as $criterion)
        {
            if(false === $criterion($pSelect))
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