<?php

namespace Elixir\Module\AppBase;

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
        
        $services = $app->locateClass('(@AppBase)\DI\Services');
        $servicesFilter = $app->locateClass('(@AppBase)\DI\ServicesFilter');
        $servicesHelper = $app->locateClass('(@AppBase)\DI\ServicesHelper');
        $servicesValidator = $app->locateClass('(@AppBase)\DI\ServicesValidator');
        
        $this->_container->load(new $services());
        $this->_container->load(new $servicesFilter());
        $this->_container->load(new $servicesHelper());
        $this->_container->load(new $servicesValidator());
        
        /************ LISTENERS ************/
        
        $subscribers = $app->locateClass('(@AppBase)\Listener\Listeners');
        $this->_dispatcher->addSubscriber(new $subscribers($this->_container));
    }
}