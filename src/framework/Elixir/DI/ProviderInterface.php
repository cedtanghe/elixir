<?php

namespace Elixir\DI;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface ProviderInterface 
{
    /**
     * @param ContainerInterface $pContainer
     */
    public function load(ContainerInterface $pContainer);
}
