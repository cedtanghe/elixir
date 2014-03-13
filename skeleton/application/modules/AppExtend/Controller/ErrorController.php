<?php

namespace AppExtend\Controller;

use Elixir\MVC\Controller\ControllerAbstract;

class ErrorController extends ControllerAbstract
{
    public function indexAction()
    {
        return $this->helper('helper.forward')->to('(@[MODULE])', null, null, array(), false);
    }
}