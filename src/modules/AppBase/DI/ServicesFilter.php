<?php

namespace Elixir\Module\AppBase\DI;

use Elixir\DI\ContainerInterface;
use Elixir\DI\ProviderInterface;
use Elixir\Facade\Filter;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class ServicesFilter implements ProviderInterface
{
    /**
     * @see ProviderInterface::load()
     */
    public function load(ContainerInterface $pContainer) 
    {
        Filter::register();
    }
}
