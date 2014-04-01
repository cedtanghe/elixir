<?php

namespace Elixir\Module\Application;

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
        
        $services = $app->locateClass('(@Application)\DI\Services');
        $servicesFilter = $app->locateClass('(@Application)\DI\ServicesFilter');
        $servicesHelper = $app->locateClass('(@Application)\DI\ServicesHelper');
        $servicesValidator = $app->locateClass('(@Application)\DI\ServicesValidator');
        
        $this->_container->load(new $services());
        $this->_container->load(new $servicesFilter());
        $this->_container->load(new $servicesHelper());
        $this->_container->load(new $servicesValidator());
        
        /************ LISTENERS ************/
        
        $subscribers = $app->locateClass('(@Application)\Listener\Listeners');
        $this->_dispatcher->addSubscriber(new $subscribers($this->_container));
    }
}