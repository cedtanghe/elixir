<?php

namespace Elixir\DI;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

interface ProviderInterface 
{
    /**
     * @param ContainerInterface $pContainer
     */
    public function load(ContainerInterface $pContainer);
}
