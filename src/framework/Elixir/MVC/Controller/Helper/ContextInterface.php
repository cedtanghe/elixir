<?php

namespace Elixir\MVC\Controller\Helper;

use Elixir\MVC\Controller\ControllerInterface;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

interface ContextInterface
{
    /**
     * @param ControllerInterface $pController
     */
    public function setController(ControllerInterface $pController);
}