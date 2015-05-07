<?php

namespace Elixir\DI;

use Elixir\DI\ContainerInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
abstract class ExtenderAbstract
{
    /**
     * @param mixed $service
     * @param ContainerInterface $container
     * @return mixed;
     */
    abstract protected function call($service, ContainerInterface $container);
    
    /**
     * @ignore
     */
    public function __invoke($service, ContainerInterface $container)
    {
        return $this->call($service, $container);
    }
}
