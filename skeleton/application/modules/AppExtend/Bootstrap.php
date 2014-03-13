<?php

namespace AppExtend;

use Elixir\MVC\Module\ModuleAbstract;

class Bootstrap extends ModuleAbstract
{
    public function getParent() 
    {
        return 'Application';
    }
    
    public function boot() 
    {
        // Not yet
    }
}