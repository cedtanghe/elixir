<?php

namespace [MODULE]\Controller;

use Elixir\Module\Application\Controller\ErrorController as ParentErrorController;

class ErrorController extends ParentErrorController
{
    protected function render(array $pData, $pStatusCode)
    {
        return $this->helper('helper.render')->renderResponse(
            null,
            $pData,
            $pStatusCode
        );
    }
}