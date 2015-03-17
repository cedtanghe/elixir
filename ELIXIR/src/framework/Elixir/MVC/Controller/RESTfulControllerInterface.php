<?php

namespace Elixir\MVC\Controller;

use Elixir\HTTP\Request;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface RESTfulControllerInterface
{
    /**
     * @param string $pMethod
     * @return string
     */
    public function getRestFulMethodName($pMethod, Request $pRequest = null);
}
