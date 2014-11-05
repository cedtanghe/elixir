<?php

namespace Elixir\DB\ORM;

use Elixir\DB\ORM\Relation\Pivot;
use Elixir\DB\ORM\RepositoryInterface;
use Elixir\DB\ORM\Select;
use Elixir\DB\SQL\JoinClause;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class EagerLoad
{
    /**
     * @var string
     */
    const REFERENCE_KEY = '_pivot';
    
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
     * @param string|RepositoryInterface $pTarget
     * @param string $pForeignKey
     * @param string $pOtherKey
     * @param Pivot $pPivot
     */
    public function __construct($pTarget, 
                                $pForeignKey, 
                                $pOtherKey = null,
                                Pivot $pPivot = null) 
    {
        $this->_target = $pTarget;
        $this->_foreignKey = $pForeignKey;
        $this->_otherKey = $pOtherKey;
        $this->_pivot = $pPivot;
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
     * @return EagerLoad
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
     * @return EagerLoad
     */
    public function addCriterion(callable $pCriterion)
    {
        $this->_criterions[] = $pCriterion;
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
     * @param string $pMember
     * @param array $pRepositories
     * @param array $pWith
     */
    public function sync($pMember, array $pRepositories, array $pWith = array())
    {
        $this->_otherKey = $this->_otherKey ?: $pRepositories[0]->getPrimaryKey();
        
        if(!$this->_target instanceof RepositoryInterface)
        {
            $this->_target = new $this->_target();
            $this->_target->setConnectionManager($pRepositories[0]->getConnectionManager());
        }
        
        $select = $this->_target->select();
        
        foreach($pWith as $member => $eagerLoad)
        {
            $select->with($member, $eagerLoad);
        }
        
        if(false !== $this->performConstraints($select))
        {
            if(false !== $this->performCriterions($select))
            {
                $loaded = $select->all();
                $key = $this->_pivot ? self::REFERENCE_KEY : $this->_foreignKey;
                $repartions = [];
                
                foreach($loaded as $r)
                {
                    foreach($pRepositories as $repository)
                    {
                        $compare = $repository->get($this->_otherKey);
                        
                        if($r->get($key) == $compare)
                        {
                            if(isset($repartions[$compare]))
                            {
                                $repartions[$compare] = (array)$repartions[$compare];
                                $repartions[$compare][] = $r;
                            }
                            else
                            {
                                $repartions[$compare] = $r;
                            }
                        }
                    }
                }
                
                foreach($repartions as $compare => $value)
                {
                    foreach($pRepositories as $repository)
                    {
                        if($repository->get($this->_otherKey) == $compare)
                        {
                            $repository->$pMember = $value;
                        }
                    }
                }
            }
        }
    }
    
    /**
     * @param Select $pSelect
     * @param array $pRepositories
     * @return boolean
     */
    protected function performConstraints(Select $pSelect, array $pRepositories)
    {
        $values = [];
        
        foreach($pRepositories as $repository)
        {
            $value = $repository->get($this->_otherKey);
            
            if(null !== $value)
            {
                $values[] = $value;
            }
        }
        
        if(count($values) == 0)
        {
            return false;
        }
        
        if(null !== $this->_pivot)
        {
            $pSelect->join(
                $this->_pivot->getPivot(),
                function(JoinClause $pSQL) use($values)
                {
                    $pSQL->on(
                        sprintf(
                            '`%s`.`%s` IN(?)', 
                            $this->_pivot->getPivot(),
                            $this->_pivot->getOtherKey()
                        ),
                        $values
                    );

                    $pSQL->on(
                        sprintf(
                            '`%s`.`%s` = `%s`.`%s`', 
                            $this->_pivot->getPivot(),
                            $this->_pivot->getForeignKey(),
                            $this->_target->getTable(),
                            $this->_foreignKey
                        )
                    );

                    foreach($this->_pivot->getCriterions() as $criterion)
                    {
                        $criterion($pSQL);
                    }
                },
                null,
                sprintf(
                    '`%s`.`%s` as `%s`',
                    $this->_pivot->getPivot(),
                    $this->_pivot->getForeignKey(),
                    self::REFERENCE_KEY
                )
            );
        }
        else
        {
            $pSelect->where(
                sprintf(
                    '`%s`.`%s` IN(?)', 
                    $this->_target->getTable(),
                    $this->_foreignKey
                ),
                $values
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
}
