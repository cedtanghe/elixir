<?php

namespace Elixir\Module\AppBase\DI;

use Elixir\DI\ContainerInterface;
use Elixir\DI\ProviderInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class ServicesValidator implements ProviderInterface
{
    /**
     * @see ProviderInterface::load()
     */
    public function load(ContainerInterface $pContainer) 
    {
        /************ VALIDATORS HERE ************/
    }
}