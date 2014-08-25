<?php

namespace Elixir\DB\ORM\Relation;

use Elixir\DB\ORM\Relation\RelationInterface;
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
    protected $_criterions = [];
    
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
     * @param callable $pCriterion
     * @return Pivot
     * @throws \InvalidArgumentException
     */
    public function addCriterion($pCriterion)
    {
        if(!is_callable($pCriterion))
        {
            throw new \InvalidArgumentException('Criterion argument must be a callable.');
        }
        
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
        $pSelect->join(
            $this->_pivot,
            function(JoinClause $pSQL) use($pRelation)
            {
                $pSQL->on(
                    sprintf(
                        '`%s`.`%s` = ?', 
                        $this->_pivot,
                        $this->_foreignKey
                    ),
                    $pRelation->getRepository()->get($pRelation->getForeignKey())
                );
                
                $pSQL->on(
                    sprintf(
                        '`%s`.`%s` = `%s`.`%s`', 
                        $this->_pivot,
                        $this->_otherKey,
                        $pRelation->getTarget()->getTable(),
                        $pRelation->getOtherKey()
                    )
                );
                
                foreach($this->_criterions as $criterion)
                {
                    $criterion($pSQL);
                }
            }
        );
    }
}
