<?php

namespace Elixir\DB\ObjectMapper\SQL;

use Elixir\DB\ObjectMapper\FindableInterface;
use Elixir\DB\ObjectMapper\RepositoryInterface;
use Elixir\DB\ObjectMapper\SQL\Relation\BaseAbstract;
use Elixir\DB\ObjectMapper\SQL\Relation\Pivot;
use Elixir\DB\Query\SQL\JoinClause;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class EagerLoad 
{
    /**
     * @var string
     */
    const REFERENCE_KEY = 'pivot_';

    /**
     * @var array
     */
    protected $repositories;

    /**
     * @var RepositoryInterface 
     */
    protected $repository;

    /**
     * @var string|RepositoryInterface 
     */
    protected $target;

    /**
     * @var string 
     */
    protected $foreignKey;

    /**
     * @var string 
     */
    protected $localKey;

    /**
     * @var Pivot 
     */
    protected $pivot;

    /**
     * @var array 
     */
    protected $criterias = [];

    /**
     * @param RepositoryInterface $repository
     * @param string|RepositoryInterface $target
     * @param array $config
     */
    public function __construct(RepositoryInterface $repository, $target, array $config = [])
    {
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

                $table = $this->repository->getStockageName() . '_' . $this->target->getStockageName();
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
     * @return RepositoryInterface
     */
    public function getRepository() 
    {
        return $this->repository;
    }

    /**
     * @return RepositoryInterface
     */
    public function getTarget()
    {
        if (!$this->target instanceof RepositoryInterface)
        {
            $class = $this->target;
            $this->target = $class::factory();
            $this->target->setConnectionManager($this->repository->getConnectionManager());
        }

        return $this->target;
    }

    /**
     * @param string $value
     * @return BaseAbstract
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
        if (null === $this->foreignKey)
        {
            // Define target
            $this->getTarget();

            if ($this->pivot) 
            {
                $this->foreignKey = $this->target->getPrimaryKey();
            } 
            else
            {
                $this->foreignKey = $this->target->getStockageName() . '_id';
            }
        }

        return $this->foreignKey;
    }

    /**
     * @param string $value
     * @return BaseAbstract
     */
    public function setLocalKey($value) 
    {
        $this->localKey = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocalKey() 
    {
        if (null === $this->localKey)
        {
            $this->localKey = $this->repository->getPrimaryKey();
        }

        return $this->localKey;
    }

    /**
     * @param Pivot $pivot
     * @return BaseAbstract
     */
    public function withPivot(Pivot $pivot)
    {
        $this->pivot = $pPivot;
        return $this;
    }

    /**
     * @return Pivot
     */
    public function getPivot()
    {
        if (null !== $this->pivot) 
        {
            // Define target
            $this->getTarget();

            if (null === $this->pivot->getForeignKey()) 
            {
                $this->pivot->setForeignKey($this->target->getStockageName() . '_id');
            }

            if (null === $this->pivot->getOtherKey()) 
            {
                $this->pivot->setOtherKey($this->target->getStockageName() . '_id');
            }
        }

        return $this->pivot;
    }

    /**
     * @param callable $criteria
     * @return BaseAbstract
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
     * @param string $member
     * @param array $repositories
     * @param array $with
     */
    public function sync($member, array $repositories, array $with = []) 
    {
        $this->repositories = $repositories;

        if (count($this->repositories) == 0) 
        {
            return;
        }

        // Define keys, pivot and target
        $this->getTarget();
        $this->getForeignKey();
        $this->getLocalKey();
        $this->getPivot();

        $findable = $this->target->find();

        foreach ($with as $member => $eagerLoad) 
        {
            $findable->with($member, $eagerLoad);
        }

        if ($this->prepareQuery($findable)) 
        {
            if ($this->extendQuery($findable)) 
            {
                $targets = $findable->all();
                $repartions = [];

                foreach ($targets as $target)
                {
                    foreach ($this->repositories as $repository) 
                    {
                        $compare = $repository->get($this->localKey);

                        if ($target->get($this->pivot ? self::REFERENCE_KEY : $this->foreignKey) == $compare)
                        {
                            if (isset($repartions[$compare])) 
                            {
                                $repartions[$compare] = (array)$repartions[$compare];
                                $repartions[$compare][] = $target;
                            } 
                            else
                            {
                                $repartions[$compare] = $target;
                            }
                        }
                    }
                }

                foreach ($repartions as $compare => $value) 
                {
                    foreach ($this->repositories as $repository)
                    {
                        if ($repository->get($this->localKey) == $compare) 
                        {
                            $repository->$member = $value;
                        }
                    }
                }
            }
        }

        $this->repositories = null;
    }

    /**
     * @param FindableInterface $findable
     * @return boolean
     */
    protected function prepareQuery(FindableInterface $findable) 
    {
        return null !== $this->pivot ? $this->parsePivot($findable) : $this->parseQuery($findable);
    }

    /**
     * @param FindableInterface $findable
     * @return boolean
     */
    protected function parsePivot(FindableInterface $findable) 
    {
        $values = [];

        foreach ($this->repositories as $repository)
        {
            $value = $repository->get($this->localKey);

            if (null !== $value) 
            {
                $values[] = $value;
            }
        }

        if (count($values) == 0) 
        {
            return false;
        }

        $findable->join(
            $this->pivot->getPivot(), 
            function(JoinClause $join) use($values) 
            {
                $join->on(
                    sprintf(
                        '`%s`.`%s` IN(?)', 
                        $this->pivot->getPivot(), 
                        $this->pivot->getOtherKey()
                    ), 
                    $values
                );

                $join->on(
                    sprintf(
                        '`%s`.`%s` = `%s`.`%s`', 
                        $this->pivot->getPivot(), 
                        $this->pivot->getForeignKey(), 
                        $this->target->getStockageName(), 
                        $this->foreignKey
                    )
                );

                foreach ($this->pivot->getCriterias() as $criteria) 
                {
                    call_user_func_array($criteria, [$join]);
                }
            }, 
            null, 
            sprintf(
                '`%s`.`%s` as `%s`', 
                $this->pivot->getPivot(), 
                $this->pivot->getForeignKey(), 
                self::REFERENCE_KEY
            )
        );

        return true;
    }

    /**
     * @param FindableInterface $findable
     * @return boolean
     */
    protected function parseQuery(FindableInterface $findable) 
    {
        $values = [];

        foreach ($this->repositories as $repository) 
        {
            $value = $repository->get($this->localKey);

            if (null !== $value) 
            {
                $values[] = $value;
            }
        }

        if (count($values) == 0) 
        {
            return false;
        }

        $findable->where(
            sprintf(
                '`%s`.`%s` IN(?)', 
                $this->target->getStockageName(), 
                $this->foreignKey
            ), 
            $values
        );

        return true;
    }

    /**
     * @param FindableInterface $findable
     * @return boolean
     */
    protected function extendQuery(FindableInterface $findable) 
    {
        foreach ($this->criterias as $criteria) 
        {
            if (false === call_user_func_array($criteria, [$findable, $this])) 
            {
                return false;
            }
        }

        return true;
    }
}
