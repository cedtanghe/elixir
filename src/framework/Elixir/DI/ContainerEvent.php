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
    const BINDED = 'binded';
    
    /**
     * @var string
     */
    const RESOLVED = 'resolved';
    
    /**
     * @var string
     */
    const TAGGED = 'taged';
    
    /**
     * @var string
     */
    const ALIASED = 'aliased';
    
    /**
     * @var string 
     */
    protected $service;
    
    /**
     * @var string 
     */
    protected $tag;
    
    /**
     * @var string 
     */
    protected $alias;

    /**
     * @see Event::__contruct()
     * @param array $params
     */
    public function __construct($pType, array $params = [])
    {
        parent::__construct($pType);
        
        $params += [
            'service' => null,
            'tag' => null,
            'alias' => null
        ];
        
        $this->service = $params['service'];
        $this->tag = $params['tag'];
        $this->alias = $params['alias'];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->service;
    }
    
    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }
    
    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }
}
