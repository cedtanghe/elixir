<?php

namespace Elixir\Module\Twig;

use Elixir\DI\ContainerInterface;
use Elixir\MVC\Module\ModuleAbstract;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Bootstrap extends ModuleAbstract
{
    /**
     * @see ModuleAbstract::boot()
     */
    public function boot() 
    {
        $this->_container->setLockMode(ContainerInterface::IGNORE_IF_ALREADY_EXISTS);
        $app = $this->_container->get('application');
        
        /************ SERVICES ************/
        
        $services = $app->locateClass('(@Twig)\DI\Services');
        $this->_container->load(new $services());
    }
}