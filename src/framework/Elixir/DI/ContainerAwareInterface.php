<?php

namespace Elixir\DI;

use Elixir\DI\ContainerInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
interface ContainerAwareInterface 
{
    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null);
}
