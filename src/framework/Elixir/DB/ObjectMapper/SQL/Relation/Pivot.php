<?php

namespace Elixir\DB\ObjectMapper\SQL\Relation;

use Elixir\DI\ContainerInterface;

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
    protected $firstKey;

    /**
     * @var string
     */
    protected $secondKey;

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
                'first_key' => null,
                'second_key' => null,
                'criterias' => []
            ], 
            $config
        );

        $this->firstKey = $config['first_key'];
        $this->secondKey = $config['second_key'];

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
    public function setFirstKey($value) 
    {
        $this->firstKey = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstKey() 
    {
        return $this->firstKey;
    }

    /**
     * @param string $value
     * @return Pivot
     */
    public function setSecondKey($value)
    {
        $this->secondKey = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getSecondKey() 
    {
        return $this->secondKey;
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
    
    /**
     * @param ContainerInterface $connectionManager
     * @param mixed $foreignValue
     * @param mixed $otherValue
     * @return boolean
     */
    public function attach(ContainerInterface $connectionManager, $foreignValue, $otherValue)
    {
        // Todo
    }
    
    /**
     * @param ContainerInterface $connectionManager
     * @param mixed $foreignValue
     * @param mixed $otherValue
     * @return boolean
     */
    public function detach(ContainerInterface $connectionManager, $foreignValue, $otherValue)
    {
        // Todo
    }
}
