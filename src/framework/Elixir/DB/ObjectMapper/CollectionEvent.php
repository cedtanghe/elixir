<?php

namespace Elixir\DB\ObjectMapper;

use Elixir\Dispatcher\Event;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class CollectionEvent extends Event
{
    /**
     * @var string
     */
    const VALUE_ADDED = 'value_added';
    
    /**
     * @var string
     */
    const VALUE_REMOVED = 'value_removed';
    
    /**
     * @var mixed 
     */
    protected $object;
    
     /**
     * @see Event::__contruct()
     * @param array $params
     */
    public function __construct($type, array $params = []) 
    {
        parent::__construct($type);

        $params = array_merge(
            ['object' => null], 
            $params
        );
        
        $this->object = $params['object'];
    }
    
    /**
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }
}
