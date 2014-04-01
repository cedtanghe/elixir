<?php

namespace Elixir\MVC\Controller\Helper;

use Elixir\MVC\Controller\ControllerInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface ContextInterface
{
    /**
     * @param ControllerInterface $pController
     */
    public function setController(ControllerInterface $pController);
}