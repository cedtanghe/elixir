<?php

namespace Elixir\Module\Facade;

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
        // Injects the dependency injection container on all facades
        
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
        
        /************ VALIDATOR MACROS ************/
        
        $validatorMacros = App::locateClass('(@Facade)\Macro\Validator');
        $validatorMacros::register();
        
        /************ FILTER MACROS ************/
        
        $filterMacros = App::locateClass('(@Facade)\Macro\Filter');
        $filterMacros::register();
    }
}