<?php

namespace Elixir\Security\Authentification;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface AuthInterface
{
    /**
     * @return Result
     */
    public function authenticate();
}