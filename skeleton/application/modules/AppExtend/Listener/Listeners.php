<?php

namespace AppExtend\Listener;

use Elixir\Dispatcher\Dispatcher;
use Elixir\Module\Application\Listener\Listeners as ParentListeners;

class Listeners extends ParentListeners
{
    public function subscribe(Dispatcher $pDispatcher)
    {   
        parent::subscribe($pDispatcher);
    }
    
    public function unsubscribe(Dispatcher $pDispatcher)
    {
        // Not yet
    }
}
