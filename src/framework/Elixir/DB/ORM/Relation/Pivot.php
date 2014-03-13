<?php

namespace Elixir\DB\ORM\Relation;

use Elixir\DB\ORM\Select;
use Elixir\DB\SQL\JoinClause;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
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
     * @param RelationInterface $pRelation
     * @param Select $pSelect
     */
    public function join(RelationInterface $pRelation, Select $pSelect)
    {
        $pivot = $this->_pivot;
        $foreignKey = $this->_foreignKey;
        $otherKey = $this->_otherKey;
        $relation = $pRelation;
        
        $pSelect->join(
            $pivot,
            function(JoinClause $pSQL) use($pivot, $foreignKey, $otherKey, $relation)
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
            }
        );
    }
}