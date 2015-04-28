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
     * @return array
     */
    public function provides();

    /**
     * @param ContainerInterface $container
     */
    public function register(ContainerInterface $container);
}
