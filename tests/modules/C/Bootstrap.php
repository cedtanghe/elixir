<?php

namespace C;

use Elixir\MVC\Module\ModuleAbstract;
use Elixir\Routing\Route;

class Bootstrap extends ModuleAbstract
{
    public function boot() 
    {
        $router = $this->_container->get('router');
        $router->getCollection()->add('C', new Route('/c/{_controller}/{_action}',
                                                     array('_module' => '(@C)'),
                                                     array('_controller' => '[a-z0-9-]+',
                                                           '_action' => '[a-z0-9-]+',
                                                           '*' => true)));
    }
}
