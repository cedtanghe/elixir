<?php

namespace Elixir\Module\AppBase;

use Elixir\DI\ContainerInterface;
use Elixir\Facade\App;
use Elixir\Facade\Cache;
use Elixir\Facade\Config;
use Elixir\Facade\DB;
use Elixir\Facade\DI;
use Elixir\Facade\Filter;
use Elixir\Facade\Log;
use Elixir\Facade\Request;
use Elixir\Facade\Validator;
use Elixir\Facade\View;
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
        
        /************ FACADES ************/
        
        App::setContainer($this->_container);
        Cache::setContainer($this->_container);
        Config::setContainer($this->_container);
        DB::setContainer($this->_container);
        DI::setContainer($this->_container);
        Filter::setContainer($this->_container);
        Log::setContainer($this->_container);
        Request::setContainer($this->_container);
        Validator::setContainer($this->_container);
        View::setContainer($this->_container);
        
        /************ LISTENERS ************/
        
        $subscribers = $app->locateClass('(@AppBase)\Listener\Listeners');
        $this->_dispatcher->addSubscriber(new $subscribers($this->_container));
    }
}
