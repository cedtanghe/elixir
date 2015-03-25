<?php

namespace Elixir\DB\ObjectMapper\SQL\Relation;

use Elixir\DB\ObjectMapper\RelationInterface;
use Elixir\DB\ObjectMapper\RepositoryInterface;
use Elixir\DB\ObjectMapper\SQL\Relation\HasOneOrManyTrait;
use Elixir\DB\ObjectMapper\SQL\Relation\Pivot;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class HasMany implements RelationInterface
{
    use HasOneOrManyTrait;
    
    /**
     * @param RepositoryInterface $repository
     * @param string|RepositoryInterface $target
     * @param array $config
     */
    public function __construct(RepositoryInterface $repository, $target, array $config = [])
    {
        $this->type = self::HAS_MANY;
        $this->repository = $repository;
        $this->target = $target;
        
        $config = array_merge(
            [
               'foreign_key' => null, 
               'other_key' => null,
               'pivot' => null,
               'criterias' => [] 
            ],
            $config
        );
        
        $this->foreignKey = $config['foreign_key'];
        $this->otherKey = $config['other_key'];
        
        if (null !== $config['pivot'])
        {
            if(!$config['pivot'] instanceof Pivot)
            {
                $config['pivot'] = new Pivot($config['pivot']);
            }
            
            $this->withPivot($config['pivot']);
        }
        
        foreach ($config['criterias'] as $criteria)
        {
            $this->addCriteria($criteria);
        }
    }
}
