<?php

namespace Elixir\DB\ObjectMapper\SQL;

use Elixir\DB\DBInterface;
use Elixir\DB\ObjectMapper\FindableExtensionInterface;
use Elixir\DB\ObjectMapper\FindableInterface;
use Elixir\DB\ObjectMapper\RepositoryEvent;
use Elixir\DB\ObjectMapper\RepositoryInterface;
use Elixir\DB\Query\QueryBuilderInterface;
use Elixir\DB\Query\SQL\SQLInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class Findable implements FindableInterface 
{
    /**
     * @var RepositoryInterface 
     */
    protected $repository;
    
    /**
     * @var DBInterface
     * @var QueryBuilderInterface
     */
    protected $DB;
    
    /**
     * @var SQLInterface
     */
    protected $SQL;
    
    /**
     * @param RepositoryInterface $repository
     * @param mixed $options
     */
    public function __construct(RepositoryInterface $repository, $options = null)
    {
        $this->repository = $repository;
        $this->repository->dispatch(
            new RepositoryEvent(
                RepositoryEvent::PRE_FIND, 
                ['query' => $this]
            )
        );
        
        $this->DB = $this->repository->getConnection('db.read');
        $this->SQL = $this->DB->createSelect('`' . $this->repository->getStockageName() . '`');
    }
    
    /**
     * @see FindableInterface::extend()
     */
    public function extend(FindableExtensionInterface $extension)
    {
        // Todo
    }

    /**
     * @param string $part
     * @return Findable
     */
    public function reset($part)
    {
        // Todo
        return $this;
    }

    /**
     * @see FindableInterface::has()
     */
    public function has() 
    {
        return $this->count() > 0;
    }

    /**
     * @see FindableInterface::count()
     */
    public function count()
    {
        // Todo
    }

    /**
     * @see FindableInterface::raw()
     */
    public function raw() 
    {
        // Todo
        $this->repository->dispatch(new RepositoryEvent(RepositoryEvent::FIND));
    }

    /**
     * @see FindableInterface::one()
     */
    public function one() 
    {
        $this->SQL->limit(1);
        $repositories = $this->all();
        
        return count($repositories) > 0 ? $repositories[0] : null;
    }
    
    /**
     * @see FindableInterface::all()
     */
    public function all()
    {
        // Todo
        $this->repository->dispatch(new RepositoryEvent(RepositoryEvent::FIND));
    }
    
    /**
     * @return string
     */
    public function __toString() 
    {
        return $this->SQL->render();
    }
}
