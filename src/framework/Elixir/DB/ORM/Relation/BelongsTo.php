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

class BelongsTo implements RelationInterface
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
     */
    public function __construct(RepositoryInterface $pRepository, 
                                $pTarget, 
                                $pForeignKey, 
                                $pOtherKey = null, 
                                Pivot $pPivot = null) 
    {
        $this->_repository = $pRepository;
        $this->_target = $pTarget;
        $this->_foreignKey = $pForeignKey;
        $this->_otherKey = $pOtherKey;
        $this->_pivot = $pPivot;
    }
    
    /**
     * @see RelationInterface::getType()
     */
    public function getType()
    {
        return self::BELONGS_TO;
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
     * @return BelongsTo
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
     * @return BelongsTo
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
        
        $select = $this->_target->select();
        
        if(false !== $this->eagerConstraints($select))
        {
            if(false !== $this->eagerCriterions($select))
            {
                $this->setRelated($select->one(), true);
            }
            else
            {
                $this->setRelated(null, true);
            }
        }
        else
        {
            $this->setRelated(null, true);
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
            $value = $this->_repository->get($this->_foreignKey);

            if(null === $value)
            {
                return false;
            }

            $pSelect->where(
                sprintf(
                    '`%s`.`%s` = ?', 
                    $this->_target->getTable(),
                    $this->_otherKey ?: $this->_target->getPrimaryKey()
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
}
