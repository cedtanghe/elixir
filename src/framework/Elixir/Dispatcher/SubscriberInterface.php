<?php

namespace Elixir\Dispatcher;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
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
