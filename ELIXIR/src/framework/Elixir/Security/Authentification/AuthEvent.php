<?php

namespace Elixir\Security\Authentification;

use Elixir\Dispatcher\Event;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class AuthEvent extends Event
{
    /**
     * @var string
     */
    const UPDATE = 'update';
    
    /**
     * @var string
     */
    const IDENTITY_REMOVED = 'identity_removed';
}
