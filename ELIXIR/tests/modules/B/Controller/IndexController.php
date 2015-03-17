<?php

namespace B\Controller;

use Elixir\MVC\Controller\RESTFulControllerAbstract;

class IndexController extends RESTFulControllerAbstract
{
    public function getIndexAction()
    {
        return 'Hello world';
    }
}
