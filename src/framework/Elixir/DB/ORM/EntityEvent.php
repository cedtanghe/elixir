<?php

namespace Elixir\DB\ORM;

use Elixir\DB\ORM\EntityInterface;
use Elixir\Dispatcher\Event;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class EntityEvent extends Event
{
    /**
     * @var string
     */
    const CREATE_ENTITY = 'create_entity';
    
    /**
     * @var string|EntityInterface 
     */
    protected $entity;
    
     /**
     * @see Event::__contruct()
     * @param array $params
     */
    public function __construct($type, array $params = []) 
    {
        parent::__construct($type);

        $params = array_merge(
            ['entity' => null], 
            $params
        );
        
        $this->entity = $params['entity'];
    }
    
    /**
     * @return string|EntityInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }
    
    /**
     * @param string|EntityInterface $value
     */
    public function setEntity($value) 
    {
        $this->entity = $value;
    }
}
