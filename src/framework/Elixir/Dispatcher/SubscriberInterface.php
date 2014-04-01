<?php

namespace Elixir\Dispatcher;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface SubscriberInterface 
{
    /**
     * @param Dispatcher $pDispatcher
     */
    public function subscribe(Dispatcher $pDispatcher);
    
    /**
     * @param Dispatcher $pDispatcher
     */
    public function unsubscribe(Dispatcher $pDispatcher);
}
