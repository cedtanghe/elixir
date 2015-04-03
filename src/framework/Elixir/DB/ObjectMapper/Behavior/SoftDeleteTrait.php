<?php

namespace Elixir\DB\ObjectMapper\Model\Behavior;

use Elixir\DB\ObjectMapper\RepositoryEvent;
use Elixir\DB\ObjectMapper\SQL\Extension\SoftDelete;
use Elixir\DB\Query\QueryBuilderInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
trait SoftDeleteTrait 
{
    /**
     * @var boolean
     */
    protected $forceDeleting = false;

    /**
     * @throws \LogicException
     * @throws \RuntimeException
     */
    public function bootSoftDeleteTrait() 
    {
        $DB = $this->getConnection('db.write');
        
        if (!$DB instanceof QueryBuilderInterface)
        {
            throw new \LogicException(
                'This class requires the db object implements the interface "\Elixir\DB\Query\QueryBuilderInterface" for convenience.'
            );
        }

        $driver = $DB->getDriver();

        switch ($driver) 
        {
            case QueryBuilderInterface::DRIVER_MYSQL:
            case QueryBuilderInterface::DRIVER_SQLITE:
                $this->addListener(RepositoryEvent::PRE_FIND, function(RepositoryEvent $e)
                {
                    $findable = $e->getQuery();
                    $findable->extend(new SoftDelete($this));
                });
                break;
            default:
                throw new \RuntimeException(sprintf('The driver "%s" is not supported by this behavior.', $driver));
        }
        
        $this->addListener(RepositoryEvent::DEFINE_FILLABLE, function(RepositoryEvent $e) 
        {
            $this->{$this->getDeletedColumn()} = $this->getIgnoreValue();
        });

        $this->addListener(RepositoryEvent::PRE_DELETE, function(RepositoryEvent $e) 
        {
            if (!$this->forceDeleting)
            {
                $this->{$this->getDeletedColumn()} = date($this->getDeletedFormat());
                $result = $this->save();
                
                $e->setQueryExecuted(true);
                $e->setQuerySuccess($result);
            }
        });
    }
    
    /**
     * @return string
     */
    public function getDeletedColumn()
    {
        return 'deleted_at';
    }
    
    /**
     * @return string
     */
    public function getDeletedFormat()
    {
        return 'Y-m-d H:i:s';
    }
    
    /**
     * @return boolean
     */
    public function isTrashed()
    {
        return $this->getDeletedColumn() === $this->getIgnoreValue();
    }

    /**
     * @return boolean
     */
    public function forceDelete() 
    {
        $this->forceDeleting = true;
        $result = $this->delete();
        $this->forceDeleting = false;
        
        return $result;
    }

    /**
     * @return boolean
     */
    public function restore() 
    {
        if ($this->isTrashed())
        {
            $this->{$this->getDeletedColumn()} = $this->getIgnoreValue();
            return $this->save();
        }

        return true;
    }
}
