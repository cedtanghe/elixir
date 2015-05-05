<?php

namespace Elixir\DI;

use Elixir\Dispatcher\Event;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class ContainerEvent extends Event 
{
    /**
     * @var string
     */
    const CREATED = 'created';
    
    /**
     * @var string 
     */
    protected $name;

    /**
     * @see Event::__contruct()
     * @param array $params
     */
    public function __construct($pType, array $params = [])
    {
        parent::__construct($pType);
        
        $params += ['name' => null];
        $this->name = $params['name'];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
