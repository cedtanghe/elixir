<?php

namespace Elixir\Security\Authentification;

use Elixir\Dispatcher\Event;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class AuthEvent extends Event
{
    /**
     * @var string
     */
    const IDENTITY_REMOVED = 'identity_removed';
}
