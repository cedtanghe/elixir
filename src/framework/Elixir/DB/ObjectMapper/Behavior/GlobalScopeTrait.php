<?php

namespace Elixir\DB\ObjectMapper\Model\Behavior;

use Elixir\DB\ObjectMapper\RepositoryEvent;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
trait GlobalScopeTrait
{
    /**
     * @var array
     */
    protected $globalScope = ['scopes' => []];
    
    public function bootGlobalScopeTrait()
    {
        $this->addListener(RepositoryEvent::PRE_FIND, function(RepositoryEvent $e)
        {
            $findable = $e->getQuery();
            
            foreach ($this->globalScope['scopes'] as $method)
            {
                $findable->scope($method);
            }
        });
    }
    
    /**
     * @param string $method
     */
    public function addGlobalScope($method)
    {
        if (!in_array($method, $this->scopes))
        {
            $this->globalScope['scopes'][] = $method;
        }
    }
    
    /**
     * @return array
     */
    public function getGlobalScopes()
    {
        return $this->globalScope['scopes'];
    }
}
