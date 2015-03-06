<?php

namespace Elixir\DB\ORM\Model;

use Elixir\DB\ORM\Entity;
use Elixir\DB\ORM\EntityEvent;
use Elixir\DB\ORM\Model\ModelEvent;
use Elixir\DB\ORM\RepositoryInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
abstract class ModelAbstract extends Entity implements RepositoryInterface
{
    public function __construct(ContainerInterface $manager = null, array $data = [])
    {
        parent::__construct(null);
        
        $this->addListener(EntityEvent::CREATE_ENTITY, function(EntityEvent $e)
        {
            $entity = $e->getEntity();
            $entity = new $entity($this->_connectionManager);
            
            $e->setEntity($entity);
        });
        
        // Fill columns
        $this->state = self::FILLABLE;
        $this->defineColumns();
        $this->dispatch(new ModelEvent(ModelEvent::DEFINE_COLUMNS));

        // Fill guarded
        $this->state = self::GUARDED;
        $this->defineGuarded();
        $this->dispatch(new ModelEvent(ModelEvent::DEFINE_GUARDED));

        if (!empty($data)) 
        {
            $this->hydrate($data, ['raw' => true, 'sync' => true]);
        }
    }
    
    /**
     * Declares columns
     */
    abstract protected function defineColumns();
    
    /**
     * Declares relations and others
     */
    protected function defineGuarded(){}
}
