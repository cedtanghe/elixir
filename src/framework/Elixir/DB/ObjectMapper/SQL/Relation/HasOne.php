<?php

namespace Elixir\DB\ObjectMapper\SQL\Relation;

use Elixir\DB\ObjectMapper\BaseAbstract;
use Elixir\DB\ObjectMapper\RepositoryInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class HasOne extends BaseAbstract 
{
    /**
     * @param RepositoryInterface $repository
     * @param string|RepositoryInterface $target
     * @param array $config
     */
    public function __construct(RepositoryInterface $repository, $target, array $config = []) 
    {
        $this->type = self::HAS_ONE;
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
        
        if (false !== $config['pivot'])
        {
            $this->pivot = $config['pivot'];
        }

        foreach ($config['criterias'] as $criteria) 
        {
            $this->addCriteria($criteria);
        }
    }
}
