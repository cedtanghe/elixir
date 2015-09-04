<?php

namespace Elixir\DI;

use Elixir\DI\ContainerInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
interface ProviderInterface 
{
    /**
     * @return boolean
     */
    public function isDeferred();
    
    /**
     * @param ContainerInterface $container
     */
    public function register(ContainerInterface $container);
    
    /**
     * @param string $service
     * @return boolean
     */
    public function provided($service);
    
    /**
     * @return array
     */
    public function provides();
}
