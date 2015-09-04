<?php

namespace Elixir\DI;

use Elixir\DI\ProviderInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
abstract class ProviderAbstract implements ProviderInterface 
{
    /**
     * @var boolean
     */
    protected $deferred = false;
    
    /**
     * @var array
     */
    protected $provides = [];
    
    /**
     * @see ProviderInterface::isDeferred()
     */
    public function isDeferred()
    {
        return $this->deferred;
    }
    
    /**
     * @see ProviderInterface::provided()
     */
    public function provided($service)
    {
        return in_array($service, $this->provides());
    }
    
    /**
     * @see ProviderInterface::provides()
     */
    public function provides()
    {
        return $this->provides;
    }
}
