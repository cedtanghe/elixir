<?php

namespace Elixir\Security\Authentification;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

interface AuthInterface
{
    /**
     * @return boolean
     */
    public function authenticate();
}