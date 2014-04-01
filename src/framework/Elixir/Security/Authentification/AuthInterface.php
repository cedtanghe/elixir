<?php

namespace Elixir\Security\Authentification;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface AuthInterface
{
    /**
     * @return boolean
     */
    public function authenticate();
}