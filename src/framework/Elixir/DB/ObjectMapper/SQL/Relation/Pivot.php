<?php

namespace Elixir\DB\ObjectMapper\SQL\Relation;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class Pivot 
{
    /**
     * @var string
     */
    protected $pivot;

    /**
     * @var string
     */
    protected $foreignKey;

    /**
     * @var string
     */
    protected $otherKey;

    /**
     * @var array 
     */
    protected $criterias = [];

    /**
     * @param string $pivot
     * @param array $config
     */
    public function __construct($pivot, array $config = [])
    {
        $this->pivot = $pivot;
        
        $config = array_merge(
            [
               'foreign_key' => null, 
               'other_key' => null,
               'criterias' => [] 
            ],
            $config
        );
        
        $this->foreignKey = $config['foreign_key'];
        $this->otherKey = $config['other_key'];
        
        foreach ($config['criterias'] as $criteria)
        {
            $this->addCriteria($criteria);
        }
    }

    /**
     * @return string
     */
    public function getPivot()
    {
        return $this->pivot;
    }
    
    /**
     * @param string $value
     * @return Pivot
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
        return $this->foreignKey;
    }
    
    /**
     * @param string $value
     * @return Pivot
     */
    public function setOtherKey($value)
    {
        $this->otherKey = $value;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getOtherKey()
    {
        return $this->otherKey;
    }

    /**
     * @param callable $criteria
     * @return Pivot
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
}
