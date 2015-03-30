<?php

namespace Elixir\DB\ObjectMapper\SQL\Relation;

use Elixir\DB\ObjectMapper\RepositoryInterface;
use Elixir\DB\ObjectMapper\SQL\Relation\BaseAbstract;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class BelongsToMany extends BaseAbstract 
{
    /**
     * @param RepositoryInterface $repository
     * @param string|RepositoryInterface $target
     * @param array $config
     */
    public function __construct(RepositoryInterface $repository, $target, array $config = [])
    {
        $this->type = self::BELONGS_TO_MANY;
        $this->repository = $repository;
        $this->target = $target;
        
        $config = array_merge(
            [
                'foreign_key' => null,
                'local_key' => null,
                'pivot' => null,
                'criterias' => []
            ], 
            $config
        );

        $this->foreignKey = $config['foreign_key'];
        $this->localKey = $config['local_key'];
        $this->pivot = null !== $config['pivot'] && false !== $config['pivot'] ? $config['pivot'] : true;

        foreach ($config['criterias'] as $criteria)
        {
            $this->addCriteria($criteria);
        }
    }
}
