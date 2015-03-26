<?php

namespace Elixir\DB\ObjectMapper\SQL\Relation;

use Elixir\DB\ObjectMapper\CollectionEvent;
use Elixir\DB\ObjectMapper\FindableInterface;
use Elixir\DB\ObjectMapper\RelationAbstract;
use Elixir\DB\ObjectMapper\RepositoryInterface;
use Elixir\DB\ObjectMapper\SQL\Relation\Pivot;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class BelongsTo extends RelationAbstract
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
            if(true === $config['pivot'])
            {
                if (!$this->target instanceof RepositoryInterface) 
                {
                    $class = $this->target;
                    $this->target = $class::factory();
                    $this->target->setConnectionManager($this->repository->getConnectionManager());
                }
                
                $table = $this->target->getStockageName() . '_' . $this->repository->getStockageName();
                $config['pivot'] = new Pivot($table);
            }
            else if(!$config['pivot'] instanceof Pivot)
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
     * @param FindableInterface $findable
     * @return boolean
     */
    protected function parseQuery(FindableInterface $findable) 
    {
        $this->foreignKey = $this->foreignKey ?: $this->repository->getPrimaryKey();
        $this->localKey = $this->localKey ?: $this->repository->getStockageName() . '_id';

        $value = $this->repository->get($this->localKey);

        if (null === $value)
        {
            return false;
        }

        $findable->where(
            sprintf(
                '`%s`.`%s` = ?', 
                $this->target->getStockageName(), 
                $this->foreignKey
            ),
            $value
        );

        return true;
    }
    
    /**
     * @see RelationAbstract::onValueAdded();
     */
    public function onValueAdded(CollectionEvent $e){}

    /**
     * @see RelationAbstract::onValueRemoved();
     */
    public function onValueRemoved(CollectionEvent $e){}
}
