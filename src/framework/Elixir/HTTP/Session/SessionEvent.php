<?php

namespace Elixir\HTTP\Session;

use Elixir\Dispatcher\Event;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class SessionEvent extends Event
{
    /**
     * @var string
     */
    const CLEAR = 'clear';
}