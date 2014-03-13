<?php

namespace A;

use Elixir\MVC\Module\ModuleAbstract;
use Elixir\Routing\Route;

class Bootstrap extends ModuleAbstract
{
    public function boot() 
    {
        $router = $this->_container->get('router');
        $router->getCollection()->add('A', new Route('/a/{_controller}/{_action}',
                                                     array('_module' => '(@A)'),
                                                     array('_controller' => '[a-z0-9-]+',
                                                           '_action' => '[a-z0-9-]+',
                                                           '*' => true)));
    }
}
