<?php

namespace {NAMESPACE}\Controller;

use Elixir\Module\AppBase\Controller\ErrorController as ParentErrorController;

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
