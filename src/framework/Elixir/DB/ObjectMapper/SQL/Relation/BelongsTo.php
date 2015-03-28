<?php

namespace Elixir\DB\ObjectMapper\SQL\Relation;

use Elixir\DB\ObjectMapper\BaseAbstract;
use Elixir\DB\ObjectMapper\CollectionEvent;
use Elixir\DB\ObjectMapper\RepositoryInterface;
use Elixir\DB\ObjectMapper\SQL\Relation\Pivot;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class BelongsTo extends BaseAbstract 
{
    /**
     * @param RepositoryInterface $repository
     * @param string|RepositoryInterface $target
     * @param array $config
     */
    public function __construct(RepositoryInterface $repository, $target, array $config = []) 
    {
        $this->type = self::BELONGS_TO;
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

        if (null !== $config['pivot'] && false !== $config['pivot'])
        {
            if (true === $config['pivot'])
            {
                // Define target
                $this->getTarget();

                $table = $this->target->getStockageName() . '_' . $this->repository->getStockageName();
                $config['pivot'] = new Pivot($table);
            } 
            else if (!$config['pivot'] instanceof Pivot)
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

    /**
     * @return string
     */
    public function getForeignKey()
    {
        if (null === $this->foreignKey)
        {
            if ($this->pivot)
            {
                $this->foreignKey = $this->getTarget()->getPrimaryKey();
            } 
            else 
            {
                $this->foreignKey = $this->repository->getPrimaryKey();
            }
        }

        return $this->foreignKey;
    }

    /**
     * @return string
     */
    public function getLocalKey() 
    {
        if (null === $this->localKey)
        {
            $this->localKey = $this->repository->getStockageName() . '_id';
        }

        return $this->localKey;
    }

    /**
     * @see BaseAbstract::objectAdded();
     */
    protected function objectAdded($object) {}

    /**
     * @see BaseAbstract::objectRemoved();
     */
    protected function objectRemoved($object) {}
}
