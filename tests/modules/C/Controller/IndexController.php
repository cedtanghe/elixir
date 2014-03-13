<?php

namespace C\Controller;

use Elixir\MVC\Controller\ControllerAbstract;
use Elixir\Helper\Forward;

class IndexController extends ControllerAbstract
{
    protected function getControllerHelpers() 
    {
        $helpers = array();
        
        $helpers['helper.forward'] = function()
        {
            return new Forward();
        };
        
        return $helpers;
    }

    public function indexAction()
    {
        return $this->forward('A');
    }
}
