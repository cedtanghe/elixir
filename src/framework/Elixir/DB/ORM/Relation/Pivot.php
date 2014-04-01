<?php

namespace Elixir\DB\ORM\Relation;

use Elixir\DB\ORM\Select;
use Elixir\DB\SQL\JoinClause;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Pivot
{
    /**
     * @var string
     */
    protected $_pivot;
    
    /**
     * @var string
     */
    protected $_foreignKey;
    
    /**
     * @var string
     */
    protected $_otherKey;
    
    /**
     * @var array 
     */
    protected $_criterions = array();
    
    /**
     * @param string $pPivot
     * @param string $pForeignKey
     * @param string $pOtherKey
     */
    public function __construct($pPivot, $pForeignKey, $pOtherKey)
    {
        $this->_pivot = $pPivot;
        $this->_foreignKey = $pForeignKey;
        $this->_otherKey = $pOtherKey;
    }
    
    /**
     * @return string
     */
    public function getPivot()
    {
        return $this->_pivot;
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
     * @param \Closure $pCriterion
     * @return Pivot
     */
    public function addCriterion(\Closure $pCriterion)
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
     * @param RelationInterface $pRelation
     * @param Select $pSelect
     */
    public function join(RelationInterface $pRelation, Select $pSelect)
    {
        $pivot = $this->_pivot;
        $foreignKey = $this->_foreignKey;
        $otherKey = $this->_otherKey;
        $relation = $pRelation;
        $criterions = $this->_criterions;
        
        $pSelect->join(
            $pivot,
            function(JoinClause $pSQL) use($pivot, $foreignKey, $otherKey, $relation, $criterions)
            {
                $pSQL->on(
                    sprintf(
                        '`%s`.`%s` = ?', 
                        $pivot,
                        $foreignKey
                    ),
                    $relation->getRepository()->get($relation->getForeignKey())
                );
                
                $pSQL->on(
                    sprintf(
                        '`%s`.`%s` = `%s`.`%s`', 
                        $pivot,
                        $otherKey,
                        $relation->getTarget()->getTable(),
                        $relation->getOtherKey()
                    )
                );
                
                foreach($criterions as $contraint)
                {
                    $contraint($pSQL);
                }
            }
        );
    }
}