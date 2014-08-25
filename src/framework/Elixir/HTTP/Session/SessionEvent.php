<?php

namespace Elixir\HTTP\Session;

use Elixir\Dispatcher\Event;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class SessionEvent extends Event
{
    /**
     * @var string
     */
    const CLEAR = 'clear';
}
