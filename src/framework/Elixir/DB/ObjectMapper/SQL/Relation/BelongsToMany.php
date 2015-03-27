<?php

namespace Elixir\DB\ObjectMapper\SQL\Relation;

use Elixir\DB\ObjectMapper\CollectionEvent;
use Elixir\DB\ObjectMapper\RepositoryInterface;
use Elixir\DB\ObjectMapper\SQL\Relation\BaseAbstract;
use Elixir\DB\ObjectMapper\SQL\Relation\Pivot;

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

        if (!$config['pivot'] instanceof Pivot) 
        {
            if (null === $config['pivot'] || true === $config['pivot']) 
            {
                // Define target
                $this->getTarget();

                $table = $this->target->getStockageName() . '_' . $this->repository->getStockageName();
                $config['pivot'] = new Pivot($table);
            }

            $config['pivot'] = new Pivot($config['pivot']);
        }

        $this->withPivot($config['pivot']);

        foreach ($config['criterias'] as $criteria)
        {
            $this->addCriteria($criteria);
        }
    }

    /**
     * @see BaseAbstract::onValueAdded();
     */
    public function onValueAdded(CollectionEvent $e) {}

    /**
     * @see BaseAbstract::onValueRemoved();
     */
    public function onValueRemoved(CollectionEvent $e) {}
}
