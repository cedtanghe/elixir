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
    const START = 'start';
    
    /**
     * @var string
     */
    const CLEAR = 'clear';
    
    /**
     * @var string
     */
    const DESTROY = 'destroy';
}
