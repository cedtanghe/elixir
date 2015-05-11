<?php

namespace Elixir\DI;

use Elixir\DI\ContainerInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
abstract class InvokableAbstract
{
    /**
     * @param string $id
     * @param ContainerInterface $container
     * @return string;
     */
    abstract protected function call($id, ContainerInterface $container);
    
    /**
     * @ignore
     */
    public function __invoke($service, ContainerInterface $container)
    {
        return $this->call($id, $container);
    }
}
