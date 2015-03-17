<?php

namespace A\Controller;

use Elixir\MVC\Controller\ControllerAbstract;

class IndexController extends ControllerAbstract
{
    public function indexAction()
    {
        return 'Hello world from module "A"';
    }
}
