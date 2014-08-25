<?php

namespace {NAMESPACE}\Controller;

use Elixir\MVC\Controller\ControllerAbstract;

class IndexController extends ControllerAbstract
{
    public function indexAction()
    {
        return $this->helper('helper.render')->renderResponse(null, []);
    }
}
