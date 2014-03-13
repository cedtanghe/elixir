<?php

namespace Elixir\MVC\Controller;

use Elixir\HTTP\Request;
use Elixir\MVC\ApplicationInterface;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

interface ControllerResolverInterface
{
    /**
     * @param ApplicationInterface $pApplication
     * @param Request $pRequest
     * @return callable
     */
    public function getController(ApplicationInterface $pApplication, Request $pRequest);
    
    /**
     * @param Request $pRequest
     * @param callable $pController
     * @return array
     */
    public function getArguments(Request $pRequest, $pController);
}