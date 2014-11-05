<?php

namespace Elixir\DB\ORM\Relation;

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
}
