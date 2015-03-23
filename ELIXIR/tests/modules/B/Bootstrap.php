<?php

namespace B;

use Elixir\MVC\Module\ModuleAbstract;
use Elixir\Routing\Route;

class Bootstrap extends ModuleAbstract
{
    public function getParent()
    {
        return 'A';
    }
    
    public function boot() 
    {
        $router = $this->_container->get('router');
        $router->getCollection()->add('B', new Route('/b/{_controller}/{_action}',
                                                     ['_module' => '(@B)'],
                                                     ['_controller' => '[a-z0-9-]+',
                                                           '_action' => '[a-z0-9-]+',
                                                           '*' => true]));
    }
}
